FROM wordpress:php8.2-apache

LABEL org.opencontainers.image.title="Catholic Kameari Church WordPress"
LABEL org.opencontainers.image.description="WordPress image for Catholic Kameari Church, St. Francis of Assisi, Tokyo."
LABEL org.opencontainers.image.source="https://github.com/TastyHeadphones/catholic-kameari"
LABEL org.opencontainers.image.licenses="UNLICENSED"

ENV WP_MEMORY_LIMIT=256M
ENV WP_MAX_MEMORY_LIMIT=512M
ENV WORDPRESS_CONFIG_EXTRA="define('WP_MEMORY_LIMIT', '256M'); define('WP_MAX_MEMORY_LIMIT', '512M'); define('DISALLOW_FILE_EDIT', true); define('WP_AUTO_UPDATE_CORE', 'minor'); define('FS_METHOD', 'direct'); define('WP_ENVIRONMENT_TYPE', getenv('WP_ENVIRONMENT_TYPE') ?: 'production'); date_default_timezone_set('Asia/Tokyo');"

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends ca-certificates curl unzip; \
    mkdir -p /tmp/wp-downloads; \
    curl -fsSL -o /tmp/wp-downloads/kadence.zip https://downloads.wordpress.org/theme/kadence.latest-stable.zip; \
    unzip -q /tmp/wp-downloads/kadence.zip -d /usr/src/wordpress/wp-content/themes; \
    for plugin in \
      kadence-blocks \
      the-events-calendar \
      contact-form-7 \
      wordpress-seo \
      updraftplus \
      wordfence \
      redirection \
      litespeed-cache \
    ; do \
      curl -fsSL -o "/tmp/wp-downloads/${plugin}.zip" "https://downloads.wordpress.org/plugin/${plugin}.latest-stable.zip"; \
      unzip -q "/tmp/wp-downloads/${plugin}.zip" -d /usr/src/wordpress/wp-content/plugins; \
    done; \
    chown -R www-data:www-data /usr/src/wordpress/wp-content/themes /usr/src/wordpress/wp-content/plugins; \
    rm -rf /tmp/wp-downloads /var/lib/apt/lists/*

COPY config/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY --chown=www-data:www-data wp-content/themes/kameari-kadence-child /usr/src/wordpress/wp-content/themes/kameari-kadence-child
COPY docker/railway-entrypoint.sh /usr/local/bin/railway-entrypoint.sh

RUN chmod +x /usr/local/bin/railway-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["railway-entrypoint.sh"]
CMD ["apache2-foreground"]

