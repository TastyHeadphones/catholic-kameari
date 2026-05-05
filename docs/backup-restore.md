# Backup And Restore Guide

## Local Database Export

```bash
mkdir -p exports
docker compose exec db mariadb-dump \
  -u root \
  -p"$MARIADB_ROOT_PASSWORD" \
  "$WORDPRESS_DB_NAME" > exports/catholic-kameari.sql
```

If your shell has not loaded `.env`, run:

```bash
set -a
source .env
set +a
```

## Local Database Restore

```bash
docker compose exec -T db mariadb \
  -u root \
  -p"$MARIADB_ROOT_PASSWORD" \
  "$WORDPRESS_DB_NAME" < exports/catholic-kameari.sql
```

## Files To Back Up

- Database.
- `wp-content/uploads/`.
- `wp-content/themes/kameari-kadence-child/`.
- Plugin settings exports where available.
- Redirection plugin export.
- `.env` values for staging, stored securely.

## Railway SQLite Mode

Single-container Railway deployments store the SQLite database at:

```text
/var/www/html/wp-content/uploads/database/.ht.sqlite
```

The Railway volume mounted at `/var/www/html/wp-content/uploads` must be backed up because it contains both uploaded media and the SQLite database. If that volume is removed or replaced, WordPress will start from an empty database and re-import only the bundled public snapshot.

## Production Backup Recommendation

Use host-level daily backups plus UpdraftPlus scheduled backups to remote storage. Keep at least:

- Daily backups for 14 days.
- Weekly backups for 8 weeks.
- Monthly backups for 12 months.

Always test restore on staging before relying on a backup process.

## Before Updating Plugins Or Theme

1. Confirm latest backup completed.
2. Update staging first.
3. Check homepage, Mass page, latest posts, forms, and mobile navigation.
4. Apply production updates during a low-traffic period.
