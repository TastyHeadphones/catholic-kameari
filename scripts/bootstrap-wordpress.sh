#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [ ! -f .env ]; then
  cp .env.example .env
  echo "Created .env from .env.example. Review passwords before production use."
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

wp() {
  docker compose run --rm wpcli "$@"
}

wait_for_wordpress() {
  local tries=0
  until curl -fsS "${WP_URL%/}/wp-admin/install.php" >/dev/null 2>&1 || curl -fsS "${WP_URL%/}" >/dev/null 2>&1; do
    tries=$((tries + 1))
    if [ "$tries" -gt 60 ]; then
      echo "WordPress did not become reachable at ${WP_URL}."
      exit 1
    fi
    sleep 2
  done
}

upsert_page() {
  local path="$1"
  local title="$2"
  local parent_path="${3:-}"
  local order="${4:-0}"
  local content="${5:-<p>移行後に本文を確認してください。</p>}"
  local slug="${path%/}"
  slug="${slug##*/}"
  local parent_id=0
  local page_id

  if [ -n "$parent_path" ]; then
    parent_id="$(wp post url-to-postid "${WP_URL%/}/${parent_path#/}" 2>/dev/null || true)"
    parent_id="${parent_id:-0}"
  fi

  page_id="$(wp post url-to-postid "${WP_URL%/}/${path#/}" 2>/dev/null || true)"

  if [ -n "$page_id" ] && [ "$page_id" != "0" ]; then
    wp post update "$page_id" \
      --post_title="$title" \
      --post_parent="$parent_id" \
      --menu_order="$order" \
      --post_status=publish >/dev/null
  else
    wp post create \
      --post_type=page \
      --post_title="$title" \
      --post_name="$slug" \
      --post_parent="$parent_id" \
      --menu_order="$order" \
      --post_status=publish \
      --post_content="$content" >/dev/null
  fi
}

docker compose up -d db wordpress
wait_for_wordpress

if ! wp core is-installed >/dev/null 2>&1; then
  wp core install \
    --url="$WP_URL" \
    --title="$WP_TITLE" \
    --admin_user="$WP_ADMIN_USER" \
    --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" \
    --skip-email
fi

wp language core install ja >/dev/null || true
wp site switch-language ja >/dev/null || true
wp option update timezone_string Asia/Tokyo >/dev/null
wp option update date_format 'Y年n月j日' >/dev/null
wp option update time_format 'G:i' >/dev/null
wp option update permalink_structure '/%year%/%postname%/' >/dev/null
wp rewrite flush --hard >/dev/null

wp theme install kadence --activate >/dev/null || true
wp theme activate kameari-kadence-child >/dev/null

wp plugin install \
  kadence-blocks \
  the-events-calendar \
  contact-form-7 \
  wordpress-seo \
  updraftplus \
  wordfence \
  redirection \
  litespeed-cache \
  --activate >/dev/null || true

home_content="$(cat wp-content/themes/kameari-kadence-child/patterns/home.html)"
upsert_page "home/" "東京大司教区カトリック亀有教会" "" 0 "$home_content"
upsert_page "about/" "カトリック亀有教会のご紹介" "" 10
upsert_page "about/introduction/" "東京大司教区カトリック亀有教会について" "about/" 11
upsert_page "about/visitors/" "初めての方へ" "about/" 12
upsert_page "about/history/" "亀有教会のあゆみ" "about/" 13
upsert_page "about/ever/" "歴代司祭・修道士" "about/" 14
upsert_page "schedule/" "ミサ・年間月間予定" "" 20
upsert_page "schedule/mass/" "ミサのご案内" "schedule/" 21
upsert_page "schedule/monthly/" "月間予定" "schedule/" 22 '<!-- wp:heading --><h2 class="wp-block-heading">月間予定</h2><!-- /wp:heading --><!-- wp:shortcode -->[kameari_posts category="monthly_schedule" posts_per_page="12"]<!-- /wp:shortcode -->'
upsert_page "schedule/annual/" "年間予定" "schedule/" 23
upsert_page "news/" "お知らせ" "" 30 '<!-- wp:heading --><h2 class="wp-block-heading">お知らせ</h2><!-- /wp:heading --><!-- wp:shortcode -->[kameari_posts category="news" posts_per_page="12"]<!-- /wp:shortcode -->'
upsert_page "topics/" "トピックス" "" 31 '<!-- wp:heading --><h2 class="wp-block-heading">トピックス</h2><!-- /wp:heading --><!-- wp:shortcode -->[kameari_posts category="topics" posts_per_page="12"]<!-- /wp:shortcode -->'
upsert_page "information/" "INFORMATION" "" 32
upsert_page "commit/" "教会活動" "" 40
upsert_page "commit/family/" "宣教協力体・教会関連リンク" "commit/" 41
upsert_page "commit/commit/" "教会組織と活動紹介" "commit/" 42
upsert_page "commit/course/" "講座・勉強会" "commit/" 43
upsert_page "memorial/" "結婚式・葬儀" "" 50
upsert_page "memorial/guidance_wedding/" "結婚式" "memorial/" 51
upsert_page "memorial/memorial/" "葬儀・共同墓地" "memorial/" 52
upsert_page "access/" "アクセス" "" 60
upsert_page "contact/" "お問い合わせ" "" 70 '<!-- wp:paragraph --><p>お問い合わせは教会事務所までお願いいたします。</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>電話 03-3606-1757</p><!-- /wp:paragraph -->'
upsert_page "privacy-policy/" "当サイトについて" "" 80

home_id="$(wp post url-to-postid "${WP_URL%/}/home/" 2>/dev/null || true)"
if [ -n "$home_id" ] && [ "$home_id" != "0" ]; then
  wp option update show_on_front page >/dev/null
  wp option update page_on_front "$home_id" >/dev/null
fi

if ! wp menu list --field=name | grep -qx 'Primary'; then
  wp menu create Primary >/dev/null
fi

for path in /schedule/mass/ /about/visitors/ /about/ /news/ /topics/ /commit/ /memorial/ /access/; do
  if ! wp menu item list Primary --format=csv | grep -q ",${path},"; then
    case "$path" in
      /schedule/mass/) wp menu item add-post Primary "$(wp post url-to-postid "${WP_URL%/}/schedule/mass/")" --title="ミサ" >/dev/null ;;
      /about/visitors/) wp menu item add-post Primary "$(wp post url-to-postid "${WP_URL%/}/about/visitors/")" --title="初めての方へ" >/dev/null ;;
      /about/) wp menu item add-post Primary "$(wp post url-to-postid "${WP_URL%/}/about/")" --title="教会紹介" >/dev/null ;;
      /news/) wp menu item add-post Primary "$(wp post url-to-postid "${WP_URL%/}/news/")" --title="お知らせ" >/dev/null ;;
      /topics/) wp menu item add-post Primary "$(wp post url-to-postid "${WP_URL%/}/topics/")" --title="トピックス" >/dev/null ;;
      /commit/) wp menu item add-post Primary "$(wp post url-to-postid "${WP_URL%/}/commit/")" --title="教会活動" >/dev/null ;;
      /memorial/) wp menu item add-post Primary "$(wp post url-to-postid "${WP_URL%/}/memorial/")" --title="結婚式・葬儀" >/dev/null ;;
      /access/) wp menu item add-post Primary "$(wp post url-to-postid "${WP_URL%/}/access/")" --title="アクセス" >/dev/null ;;
    esac
  fi
done

wp menu location assign Primary primary >/dev/null || true

echo "WordPress staging site is ready at ${WP_URL}"
echo "Admin: ${WP_URL%/}/wp-admin/"
