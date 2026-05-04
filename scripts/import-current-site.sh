#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [ ! -f .env ]; then
  echo "Missing .env. Copy .env.example to .env and run scripts/bootstrap-wordpress.sh first."
  exit 1
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

SOURCE_URL="${SOURCE_URL:-https://catholic-kameari.jp}"
CACHE_DIR="${CACHE_DIR:-.migration-cache}"
mkdir -p "$CACHE_DIR"

wp() {
  docker compose run --rm wpcli "$@"
}

fetch() {
  local endpoint="$1"
  curl -fsSL "${SOURCE_URL%/}${endpoint}"
}

json_escape() {
  jq -Rs .
}

post_exists_by_path() {
  local path="$1"
  wp post url-to-postid "${WP_URL%/}${path}" 2>/dev/null || true
}

echo "Fetching current public WordPress content from ${SOURCE_URL}..."
fetch '/wp-json/wp/v2/categories?per_page=100&_fields=id,slug,name' > "$CACHE_DIR/categories.json"
fetch '/wp-json/wp/v2/pages?per_page=100&_fields=id,date,modified,slug,parent,link,title,content' > "$CACHE_DIR/pages.json"
fetch '/wp-json/wp/v2/posts?per_page=100&_fields=id,date,modified,slug,link,title,content,categories' > "$CACHE_DIR/posts.json"

echo "Creating categories..."
rm -f "$CACHE_DIR/category-map.tsv"
jq -c '.[]' "$CACHE_DIR/categories.json" | while read -r row; do
  id="$(jq -r '.id' <<<"$row")"
  slug="$(jq -r '.slug' <<<"$row")"
  name="$(jq -r '.name' <<<"$row")"
  local_id="$(wp term list category --slug="$slug" --field=term_id --format=ids)"
  if [ -z "$local_id" ]; then
    local_id="$(wp term create category "$name" --slug="$slug" --porcelain)"
  fi
  printf '%s\t%s\n' "$id" "$local_id" >> "$CACHE_DIR/category-map.tsv"
done

echo "Importing pages..."
rm -f "$CACHE_DIR/page-map.tsv"
jq -c 'sort_by(.parent, .id)[]' "$CACHE_DIR/pages.json" | while read -r row; do
  old_id="$(jq -r '.id' <<<"$row")"
  title="$(jq -r '.title.rendered | gsub("<[^>]+>"; "")' <<<"$row")"
  slug="$(jq -r '.slug' <<<"$row")"
  old_parent="$(jq -r '.parent' <<<"$row")"
  path="$(node -e "console.log(new URL(process.argv[1]).pathname)" "$(jq -r '.link' <<<"$row")")"
  if [ "$path" = "/" ]; then
    echo "Skipping old homepage content so the redesigned front page remains active."
    continue
  fi
  date="$(jq -r '.date' <<<"$row")"
  modified="$(jq -r '.modified' <<<"$row")"
  content_file="$CACHE_DIR/page-${old_id}.html"
  jq -r '.content.rendered' <<<"$row" > "$content_file"
  parent_id=0

  if [ "$old_parent" != "0" ] && [ -f "$CACHE_DIR/page-map.tsv" ]; then
    parent_id="$(awk -v old="$old_parent" '$1 == old { print $2 }' "$CACHE_DIR/page-map.tsv")"
    parent_id="${parent_id:-0}"
  fi

  local_id="$(post_exists_by_path "$path")"
  if [ -n "$local_id" ] && [ "$local_id" != "0" ]; then
    wp post update "$local_id" \
      --post_type=page \
      --post_title="$title" \
      --post_name="$slug" \
      --post_parent="$parent_id" \
      --post_status=publish \
      --post_date="$date" \
      --post_modified="$modified" \
      --post_content="$(cat "$content_file")" >/dev/null
  else
    local_id="$(wp post create \
      --post_type=page \
      --post_title="$title" \
      --post_name="$slug" \
      --post_parent="$parent_id" \
      --post_status=publish \
      --post_date="$date" \
      --post_modified="$modified" \
      --post_content="$(cat "$content_file")" \
      --porcelain)"
  fi
  printf '%s\t%s\n' "$old_id" "$local_id" >> "$CACHE_DIR/page-map.tsv"
done

echo "Importing posts..."
jq -c '.[]' "$CACHE_DIR/posts.json" | while read -r row; do
  old_id="$(jq -r '.id' <<<"$row")"
  title="$(jq -r '.title.rendered | gsub("<[^>]+>"; "")' <<<"$row")"
  slug="$(jq -r '.slug' <<<"$row")"
  path="$(node -e "console.log(new URL(process.argv[1]).pathname)" "$(jq -r '.link' <<<"$row")")"
  date="$(jq -r '.date' <<<"$row")"
  modified="$(jq -r '.modified' <<<"$row")"
  content_file="$CACHE_DIR/post-${old_id}.html"
  jq -r '.content.rendered' <<<"$row" > "$content_file"

  category_ids="$(
    jq -r '.categories[]?' <<<"$row" | while read -r old_cat; do
      awk -v old="$old_cat" '$1 == old { print $2 }' "$CACHE_DIR/category-map.tsv"
    done | paste -sd, -
  )"

  local_id="$(post_exists_by_path "$path")"
  if [ -n "$local_id" ] && [ "$local_id" != "0" ]; then
    wp post update "$local_id" \
      --post_type=post \
      --post_title="$title" \
      --post_name="$slug" \
      --post_status=publish \
      --post_date="$date" \
      --post_modified="$modified" \
      --post_category="$category_ids" \
      --post_content="$(cat "$content_file")" >/dev/null
  else
    wp post create \
      --post_type=post \
      --post_title="$title" \
      --post_name="$slug" \
      --post_status=publish \
      --post_date="$date" \
      --post_modified="$modified" \
      --post_category="$category_ids" \
      --post_content="$(cat "$content_file")" >/dev/null
  fi
done

wp rewrite flush --hard >/dev/null

echo "Public REST API content import complete."
echo "Review ${CACHE_DIR} logs and compare against migration/content-inventory.csv."
