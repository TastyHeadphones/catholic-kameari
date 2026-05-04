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

## Publish Flow

The GitHub Actions workflow `.github/workflows/publish-ghcr.yml` builds and pushes:

- `ghcr.io/tastyheadphones/catholic-kameari:latest`
- `ghcr.io/tastyheadphones/catholic-kameari:sha-<commit-sha>`

The workflow runs on pushes to `main`, semantic version tags, and manual dispatch.

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
WORDPRESS_DB_HOST=<railway-mysql-host-and-port>
WORDPRESS_DB_NAME=<database-name>
WORDPRESS_DB_USER=<database-user>
WORDPRESS_DB_PASSWORD=<database-password>
WP_ENVIRONMENT_TYPE=production
```

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
- Import the content inventory and redirects as described in `docs/migration-plan.md`.
- Configure backups before launch.
- Connect the final domain and verify SSL.

## Notes

Railway is convenient for Dockerized deployment, but WordPress still needs persistent uploads and a reliable database backup strategy. Treat the container image as application code, and treat the database plus uploads volume as production data.

