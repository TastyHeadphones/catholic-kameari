#!/usr/bin/env bash
set -euo pipefail

is_false() {
  case "${1:-}" in
    0|false|FALSE|no|NO|off|OFF) return 0 ;;
    *) return 1 ;;
  esac
}

append_wordpress_config() {
  local extra="$1"
  if [ -n "${WORDPRESS_CONFIG_EXTRA:-}" ]; then
    export WORDPRESS_CONFIG_EXTRA="${WORDPRESS_CONFIG_EXTRA}
${extra}"
  else
    export WORDPRESS_CONFIG_EXTRA="${extra}"
  fi
}

sync_wp_content_dir() {
  local relative="$1"
  local mode="${2:-always}"
  local source="/usr/src/wordpress/wp-content/${relative}"
  local target="/var/www/html/wp-content/${relative}"

  if [ ! -d "$source" ]; then
    return
  fi

  if [ "$mode" = "missing" ] && [ -e "$target" ]; then
    return
  fi

  mkdir -p "$target"
  cp -a "${source}/." "$target/"
  chown -R www-data:www-data "$target"
}

sync_packaged_wordpress_files() {
  sync_wp_content_dir "mu-plugins"
  sync_wp_content_dir "themes/kameari-kadence-child"
  sync_wp_content_dir "themes/kadence" "missing"

  for plugin in \
    kadence-blocks \
    the-events-calendar \
    contact-form-7 \
    wordpress-seo \
    updraftplus \
    wordfence \
    redirection \
    litespeed-cache \
    sqlite-database-integration
  do
    sync_wp_content_dir "plugins/${plugin}" "missing"
  done
}

kameari_wp() {
  wp --allow-root --path=/var/www/html "$@"
}

detect_site_url() {
  if [ -n "${WP_URL:-}" ]; then
    printf '%s' "$WP_URL"
  elif [ -n "${WORDPRESS_SITE_URL:-}" ]; then
    printf '%s' "$WORDPRESS_SITE_URL"
  elif [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
    printf 'https://%s' "$RAILWAY_PUBLIC_DOMAIN"
  elif [ -n "${PORT:-}" ]; then
    printf 'http://localhost:%s' "$PORT"
  else
    printf 'https://catholic-kameari.jp'
  fi
}

wait_for_wordpress_database() {
  local tries="${KAMEARI_DB_WAIT_TRIES:-60}"
  local output

  for _ in $(seq 1 "$tries"); do
    if kameari_wp core is-installed >/dev/null 2>&1; then
      return 0
    fi

    output="$(kameari_wp core is-installed 2>&1 || true)"
    if grep -Eqi 'Error establishing a database connection|connection refused|access denied|unknown host|unknown database|SQLSTATE|could not find driver|No such file|php_network_getaddresses|connection timed out|can'\''t connect' <<<"$output"; then
      sleep 2
      continue
    fi

    return 0
  done

  echo "WordPress database was not reachable; skipping automatic install/import for now." >&2
  return 1
}

kameari_seed_content() {
  if is_false "${KAMEARI_AUTO_SEED:-1}"; then
    return
  fi

  if ! kameari_wp eval 'if (class_exists("Kameari_Content_Seeder")) { Kameari_Content_Seeder::maybe_seed(); }'; then
    echo "Automatic Catholic Kameari content import did not complete; the must-use seeder will retry on the next WordPress request." >&2
  fi

  chown -R www-data:www-data /var/www/html/wp-content/uploads /var/www/html/wp-content/mu-plugins /var/www/html/wp-content/themes 2>/dev/null || true
}

kameari_auto_install_and_seed() {
  if is_false "${KAMEARI_BOOTSTRAP_ON_START:-1}"; then
    return
  fi

  if ! command -v wp >/dev/null 2>&1; then
    echo "WP-CLI is not available; skipping automatic install/import." >&2
    return
  fi

  if ! wait_for_wordpress_database; then
    return
  fi

  if kameari_wp core is-installed >/dev/null 2>&1; then
    kameari_seed_content
    return
  fi

  if is_false "${KAMEARI_AUTO_INSTALL:-1}"; then
    echo "WordPress is not installed and KAMEARI_AUTO_INSTALL is disabled." >&2
    return
  fi

  local site_url admin_user admin_email admin_password password_file generated_password old_umask
  site_url="$(detect_site_url)"
  admin_user="${WP_ADMIN_USER:-${WORDPRESS_ADMIN_USER:-kameari_admin}}"
  admin_email="${WP_ADMIN_EMAIL:-${WORDPRESS_ADMIN_EMAIL:-admin@catholic-kameari.jp}}"
  admin_password="${WP_ADMIN_PASSWORD:-${WORDPRESS_ADMIN_PASSWORD:-}}"
  generated_password=0

  if [ -z "$admin_password" ]; then
    password_file="${KAMEARI_ADMIN_PASSWORD_FILE:-/var/www/html/wp-content/uploads/.kameari-admin-password}"
    mkdir -p "$(dirname "$password_file")"

    if [ -f "$password_file" ]; then
      admin_password="$(cat "$password_file")"
    else
      admin_password="$(php -r 'echo bin2hex(random_bytes(18));' 2>/dev/null || date +%s%N)"
      old_umask="$(umask)"
      umask 077
      printf '%s' "$admin_password" > "$password_file"
      umask "$old_umask"
      generated_password=1
    fi
  fi

  echo "Installing WordPress for ${site_url} before first start."
  if ! kameari_wp core install \
    --url="$site_url" \
    --title="${WP_TITLE:-東京大司教区カトリック亀有教会}" \
    --admin_user="$admin_user" \
    --admin_password="$admin_password" \
    --admin_email="$admin_email" \
    --skip-email; then
    echo "Automatic WordPress install failed; continuing to Apache so WordPress can show the installer or retry later." >&2
    return
  fi

  if [ "$generated_password" = "1" ]; then
    echo "Generated WordPress admin credentials for first login: user=${admin_user} password=${admin_password}" >&2
  fi

  kameari_seed_content
}

# The official WordPress Apache image expects prefork for mod_php. Some hosts or
# rebuilds can leave event/worker symlinks enabled, which makes Apache fail with
# "More than one MPM loaded." Normalize the module set before startup.
rm -f /etc/apache2/mods-enabled/mpm_event.load \
  /etc/apache2/mods-enabled/mpm_event.conf \
  /etc/apache2/mods-enabled/mpm_worker.load \
  /etc/apache2/mods-enabled/mpm_worker.conf

if [ ! -e /etc/apache2/mods-enabled/mpm_prefork.load ]; then
  a2enmod mpm_prefork >/dev/null
fi

if [ -z "${WORDPRESS_DB_HOST:-}" ] && [ -n "${MYSQLHOST:-}" ]; then
  export WORDPRESS_DB_HOST="${MYSQLHOST}${MYSQLPORT:+:${MYSQLPORT}}"
fi

if [ -z "${WORDPRESS_DB_NAME:-}" ] && [ -n "${MYSQLDATABASE:-}" ]; then
  export WORDPRESS_DB_NAME="${MYSQLDATABASE}"
fi

if [ -z "${WORDPRESS_DB_USER:-}" ] && [ -n "${MYSQLUSER:-}" ]; then
  export WORDPRESS_DB_USER="${MYSQLUSER}"
fi

if [ -z "${WORDPRESS_DB_PASSWORD:-}" ] && [ -n "${MYSQLPASSWORD:-}" ]; then
  export WORDPRESS_DB_PASSWORD="${MYSQLPASSWORD}"
fi

if [ -n "${PORT:-}" ] && [ "${PORT}" != "80" ]; then
  sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

sync_packaged_wordpress_files

if [ -z "${WORDPRESS_DB_HOST:-}" ] && [ -z "${MYSQLHOST:-}" ]; then
  echo "No MySQL variables detected. Enabling SQLite mode for single-container deployment."
  sqlite_main_file="${SQLITE_MAIN_FILE:-/var/www/html/wp-content/uploads/database/.ht.sqlite}"
  legacy_sqlite_file="/var/www/html/wp-content/database/.ht.sqlite"
  export SQLITE_MAIN_FILE="$sqlite_main_file"
  export WORDPRESS_DB_HOST="${WORDPRESS_DB_HOST:-localhost}"
  export WORDPRESS_DB_NAME="${WORDPRESS_DB_NAME:-wordpress}"
  export WORDPRESS_DB_USER="${WORDPRESS_DB_USER:-wordpress}"
  export WORDPRESS_DB_PASSWORD="${WORDPRESS_DB_PASSWORD:-wordpress}"
  append_wordpress_config "define('SQLITE_MAIN_FILE', getenv('SQLITE_MAIN_FILE') ?: '${sqlite_main_file}');"
  mkdir -p "$(dirname "$sqlite_main_file")"
  if [ -f "$legacy_sqlite_file" ] && [ ! -f "$sqlite_main_file" ]; then
    cp "$legacy_sqlite_file" "$sqlite_main_file"
  fi
  chown -R www-data:www-data "$(dirname "$sqlite_main_file")"
  if [ -f /usr/src/wordpress/wp-content/plugins/sqlite-database-integration/db.copy ]; then
    cp /usr/src/wordpress/wp-content/plugins/sqlite-database-integration/db.copy /var/www/html/wp-content/db.php
  fi
fi

if [ "${1:-}" = "apache2-foreground" ]; then
  docker-entrypoint.sh apache2ctl -t >/dev/null
  kameari_auto_install_and_seed
fi

exec docker-entrypoint.sh "$@"
