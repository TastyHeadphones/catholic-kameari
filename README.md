# Catholic Kameari Church Website Rebuild

This repository is a practical WordPress rebuild and migration kit for the official website of 東京大司教区カトリック亀有教会, Catholic Kameari Church, St. Francis of Assisi, Tokyo.

The recommendation is WordPress with Kadence Theme, Gutenberg, Kadence Blocks, The Events Calendar, Contact Form 7, Yoast SEO, UpdraftPlus, Wordfence, LiteSpeed Cache, and Redirection. This keeps the publishing workflow familiar for parish staff while allowing a modern, calm, mobile-first redesign.

[![Deploy on Railway](https://railway.com/button.svg)](https://railway.com/new?utm_medium=integration&utm_source=button&utm_campaign=catholic-kameari)

Use the button to open Railway's new project flow, then select **Deploy from GitHub repo** and choose `TastyHeadphones/catholic-kameari`. The container supports **single-container mode** (no external MySQL) via bundled SQLite, or MySQL mode for production scale. If this project is later published as a Railway template, replace the button URL with the generated template URL.

## GHCR Docker Image

The production WordPress image is published to GitHub Container Registry:

```text
ghcr.io/tastyheadphones/catholic-kameari:latest
```

The image includes WordPress PHP 8.2 Apache, the Kadence parent theme, the Catholic Kameari child theme, and the recommended plugin packages. Pushes to `main` automatically publish `latest` and `sha-<commit>` tags through `.github/workflows/publish-ghcr.yml`.

For Railway, create a new service from the Docker image above:
- **Single-container mode (recommended for quick cloud testing):** do not set any MySQL variables. The entrypoint auto-enables SQLite.
- **MySQL mode:** add a MySQL-compatible database service and set `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, and `MYSQLPASSWORD`.
In both modes, mount persistent storage at `/var/www/html/wp-content/uploads`. See `docs/railway-ghcr-deploy.md`.

The image maps Railway's MySQL variables into the `WORDPRESS_DB_*` variables expected by the official WordPress image.

The old-site content snapshot is bundled in the image at `/opt/kameari/source-content`.

Run the same Docker startup smoke test used by GitHub Actions:

```bash
./scripts/smoke-test-docker.sh
```

## What Is Included

- Docker local/staging environment for WordPress PHP 8.2 and MariaDB.
- GHCR-ready production Docker image.
- Docker smoke test for the GHCR/Railway startup path.
- Kadence child theme with the Catholic Kameari visual direction.
- Editable Gutenberg homepage pattern.
- Bootstrap script for WordPress settings, theme, plugins, menu, and required pages.
- Public REST API migration script for pages/posts when a full admin export is unavailable.
- Generated content inventory and redirect map from the current site.
- Maintenance, backup, publishing, and migration documentation.

## Quick Start

1. Copy environment values.

```bash
cp .env.example .env
```

2. Review `.env`, especially passwords and `WP_ADMIN_EMAIL`.

3. Start WordPress.

```bash
docker compose up -d
```

4. Bootstrap theme, plugins, settings, menu, and starter pages.

```bash
./scripts/bootstrap-wordpress.sh
```

5. Open the local site.

- Site: http://localhost:8080
- Admin: http://localhost:8080/wp-admin/

6. Optional development database UI.

```bash
docker compose --profile dev up -d adminer
```

Adminer will be available at http://localhost:8081.

## Content Migration

Preferred migration path:

1. In the existing WordPress admin, export all content using Tools > Export.
2. Import that WXR file into this staging site using Tools > Import > WordPress.
3. Run a media check and replace any remote image URLs.
4. Import `migration/redirection-plugin-import.csv` into the Redirection plugin.

Fallback migration path using public REST API:

```bash
./scripts/import-current-site.sh
```

The fallback preserves visible page/post content, slugs, dates, and categories, but it imports rendered HTML rather than original block editor source. Use the admin WXR export whenever possible.

## Migration Deliverables

- `migration/content-inventory.csv`: 19 pages and 88 posts discovered from the current site.
- `migration/source-content/`: full public snapshot fetched from the old site, including REST JSON, rendered page/post HTML, and 189 original media files.
- `migration/redirect-map.csv`: old URL to planned new URL map.
- `migration/redirection-plugin-import.csv`: 301 redirects for the Redirection plugin.
- `migration/manual-confirmation-notes.md`: items that must be checked before launch.
- `migration/duplicate-outdated-notes.md`: duplicate, historical, and outdated content notes.

Refresh these files after the current site changes:

```bash
./scripts/generate-migration-inventory.mjs
./scripts/fetch-current-site-content.mjs
```

## Documentation

- `docs/technical-recommendation.md`
- `docs/information-architecture.md`
- `docs/migration-plan.md`
- `docs/maintenance-guide.md`
- `docs/backup-restore.md`
- `docs/seo-accessibility.md`
- `docs/railway-ghcr-deploy.md`

## Production Recommendation

Use managed WordPress hosting with PHP 8.2+, MariaDB/MySQL, automated daily backups, SSL, staging, and server-level caching. Keep Docker for local development and staging parity, not as the only backup strategy.

For production, keep plugin count low and update monthly after a backup. Test updates on staging first.
