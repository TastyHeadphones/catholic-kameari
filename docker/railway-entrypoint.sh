#!/usr/bin/env bash
set -euo pipefail

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

if [ -z "${WORDPRESS_DB_HOST:-}" ] && [ -z "${MYSQLHOST:-}" ]; then
  echo "No MySQL variables detected. Enabling SQLite mode for single-container deployment."
  export WORDPRESS_DB_HOST="${WORDPRESS_DB_HOST:-localhost}"
  export WORDPRESS_DB_NAME="${WORDPRESS_DB_NAME:-wordpress}"
  export WORDPRESS_DB_USER="${WORDPRESS_DB_USER:-wordpress}"
  export WORDPRESS_DB_PASSWORD="${WORDPRESS_DB_PASSWORD:-wordpress}"
  export WORDPRESS_CONFIG_EXTRA="${WORDPRESS_CONFIG_EXTRA:-}
define('SQLITE_MAIN_FILE', '/var/www/html/wp-content/database/.ht.sqlite');
"
  mkdir -p /var/www/html/wp-content/database
  chown -R www-data:www-data /var/www/html/wp-content/database
  if [ -f /usr/src/wordpress/wp-content/plugins/sqlite-database-integration/db.copy ]; then
    cp /usr/src/wordpress/wp-content/plugins/sqlite-database-integration/db.copy /var/www/html/wp-content/db.php
  fi
fi

exec docker-entrypoint.sh "$@"
