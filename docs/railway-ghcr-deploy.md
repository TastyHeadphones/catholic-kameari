# Railway Deployment With GHCR

This project publishes a production WordPress image to GitHub Container Registry:

```text
ghcr.io/tastyheadphones/catholic-kameari:latest
```

The image includes:

- WordPress PHP 8.2 Apache.
- Kadence parent theme.
- Catholic Kameari Kadence child theme.
- Recommended plugin packages: Kadence Blocks, The Events Calendar, Contact Form 7, Yoast SEO, UpdraftPlus, Wordfence, Redirection, and LiteSpeed Cache.
- PHP upload configuration from `config/uploads.ini`.
- A Railway-aware entrypoint that honors Railway's `$PORT` variable.
- Automatic mapping from Railway MySQL variables to WordPress database variables.
- Automatic SQLite fallback when no MySQL or `WORDPRESS_DB_*` variables are provided.
- Automatic first-boot WordPress install and old-site content import when the database is empty.
- Apache MPM normalization so only `mpm_prefork` is loaded for WordPress `mod_php`.
- Old-site source content bundled at `/opt/kameari/source-content`.

## Publish Flow

The GitHub Actions workflow `.github/workflows/publish-ghcr.yml` builds and pushes:

- `ghcr.io/tastyheadphones/catholic-kameari:latest`
- `ghcr.io/tastyheadphones/catholic-kameari:sha-<commit-sha>`

The workflow runs on pushes to `main`, semantic version tags, and manual dispatch.

Before pushing to GHCR, the workflow runs:

```bash
./scripts/smoke-test-docker.sh
```

That script builds the Docker image, runs `apache2ctl -t`, boots the image with `PORT=8080`, and fails if Apache logs `More than one MPM loaded` or if `mpm_event`/`mpm_worker` are active.

If the package is private in GitHub Packages, make it public or configure Railway with GHCR credentials before deploying.

## Railway Setup

1. Open Railway and create a new project.
2. Add a new service using **Docker Image**.
3. Use this image:

```text
ghcr.io/tastyheadphones/catholic-kameari:latest
```

4. Choose one database mode:

For either mode, set these WordPress bootstrap variables on the WordPress service before first deploy:

```text
WP_URL=https://catholic-kameari.jp
WP_ADMIN_USER=<admin-user>
WP_ADMIN_PASSWORD=<strong-admin-password>
WP_ADMIN_EMAIL=<admin-email>
```

### Option A: Single-container mode (no external MySQL)

- Do not set `MYSQL*` or `WORDPRESS_DB_*` variables.
- On startup, the entrypoint enables the bundled `sqlite-database-integration` plugin and creates `wp-content/db.php`.
- Add a persistent volume mounted at `/var/www/html/wp-content/uploads`.
- The SQLite database is stored at `/var/www/html/wp-content/uploads/database/.ht.sqlite`, so that uploads volume is required for both media and database persistence.

Single-container mode is acceptable for quick cloud testing. For long-term production, use MySQL mode plus backups.

### Option B: MySQL mode

- Add a MySQL-compatible database service.
- Set these variables on the WordPress service:

```text
MYSQLHOST=<railway-mysql-host>
MYSQLPORT=<railway-mysql-port>
MYSQLDATABASE=<database-name>
MYSQLUSER=<database-user>
MYSQLPASSWORD=<database-password>
WP_ENVIRONMENT_TYPE=production
```

The image maps those Railway variables to `WORDPRESS_DB_HOST`, `WORDPRESS_DB_NAME`, `WORDPRESS_DB_USER`, and `WORDPRESS_DB_PASSWORD` before the official WordPress entrypoint creates `wp-config.php`.

You can also set the `WORDPRESS_DB_*` variables directly. If both are present, the explicit `WORDPRESS_DB_*` values win.

5. Add a Railway volume mounted at:

```text
/var/www/html/wp-content/uploads
```

Do not mount a volume over the entire `/var/www/html` or `/var/www/html/wp-content` directory in production, because that would hide the packaged theme and plugin files from the image.

6. Deploy the service and open the generated Railway domain.
7. On an empty database, the container installs WordPress, activates the Catholic Kameari child theme, imports the bundled source snapshot, imports media, and sets the redesigned homepage.
8. If `WP_ADMIN_PASSWORD` is not set, the container generates one, saves it at `/var/www/html/wp-content/uploads/.kameari-admin-password`, and prints it once in the Railway logs. Set `WP_ADMIN_PASSWORD` yourself for production and rotate any generated password after first login.
9. Import redirects using `migration/redirection-plugin-import.csv` in the Redirection plugin.

The automatic importer is guarded: if the database already has real posts or pages, it skips import and does not overwrite production content. Set `KAMEARI_AUTO_INSTALL=0` to disable automatic install, or `KAMEARI_AUTO_SEED=0` to disable automatic snapshot import.

## Required WordPress Follow-Up

After the first successful deploy:

- Confirm the homepage is the redesigned page.
- Confirm the old pages, posts, media, and latest monthly schedule are present.
- Confirm permalinks are `/%year%/%postname%/`.
- Import redirects from `migration/redirection-plugin-import.csv`.
- Configure backups before launch.
- Connect the final domain and verify SSL.

## Notes

Railway is convenient for Dockerized deployment, but WordPress still needs persistent uploads and a reliable database backup strategy. Treat the container image as application code, and treat the database plus uploads volume as production data.
