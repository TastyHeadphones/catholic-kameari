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

4. Add a MySQL-compatible database service.
5. Set these variables on the WordPress service:

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

6. Add a Railway volume mounted at:

```text
/var/www/html/wp-content/uploads
```

Do not mount a volume over the entire `/var/www/html` or `/var/www/html/wp-content` directory in production, because that would hide the packaged theme and plugin files from the image.

7. Deploy the service and open the generated Railway domain.
8. Complete the WordPress installer.
9. Activate the Kadence theme and then the Catholic Kameari Kadence child theme.
10. Activate the recommended plugins.
11. Import current content and redirects using the migration plan.

## Required WordPress Follow-Up

After the first successful deploy:

- Set permalinks to `/%year%/%postname%/`.
- Confirm the homepage is the redesigned page.
- Import the source content snapshot and redirects as described in `docs/migration-plan.md`.
- Configure backups before launch.
- Connect the final domain and verify SSL.

## Notes

Railway is convenient for Dockerized deployment, but WordPress still needs persistent uploads and a reliable database backup strategy. Treat the container image as application code, and treat the database plus uploads volume as production data.
