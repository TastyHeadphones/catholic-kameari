#!/usr/bin/env bash
set -euo pipefail

IMAGE_NAME="${IMAGE_NAME:-catholic-kameari:test}"
CONTAINER_NAME="${CONTAINER_NAME:-catholic-kameari-smoke}"
HOST_PORT="${HOST_PORT:-18080}"

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is required for the smoke test." >&2
  exit 127
fi

cleanup() {
  docker rm -f "$CONTAINER_NAME" >/dev/null 2>&1 || true
}

trap cleanup EXIT

docker build -t "$IMAGE_NAME" .

docker run --rm \
  -e MYSQLHOST=localhost \
  -e MYSQLPORT=3306 \
  -e MYSQLDATABASE=wordpress \
  -e MYSQLUSER=wordpress \
  -e MYSQLPASSWORD=wordpress \
  "$IMAGE_NAME" \
  apache2ctl -t

cleanup

docker run -d \
  --name "$CONTAINER_NAME" \
  -e PORT=8080 \
  -e KAMEARI_AUTO_INSTALL=0 \
  -e KAMEARI_AUTO_SEED=0 \
  -e MYSQLHOST=localhost \
  -e MYSQLPORT=3306 \
  -e MYSQLDATABASE=wordpress \
  -e MYSQLUSER=wordpress \
  -e MYSQLPASSWORD=wordpress \
  -p "${HOST_PORT}:8080" \
  "$IMAGE_NAME" >/dev/null

sleep 8

logs="$(docker logs "$CONTAINER_NAME" 2>&1 || true)"
printf '%s\n' "$logs"

if grep -qi 'More than one MPM loaded' <<<"$logs"; then
  echo "Apache MPM smoke test failed." >&2
  exit 1
fi

if ! docker exec "$CONTAINER_NAME" apache2ctl -M 2>/dev/null | grep -q 'mpm_prefork_module'; then
  echo "mpm_prefork_module is not loaded." >&2
  exit 1
fi

if docker exec "$CONTAINER_NAME" apache2ctl -M 2>/dev/null | grep -Eq 'mpm_(event|worker)_module'; then
  echo "Unexpected event/worker MPM module is loaded." >&2
  exit 1
fi

# /proc/<pid>/environ can miss environment updates performed after process start,
# so prefer proving DB host mapping through WordPress bootstrap logs.
if ! docker exec "$CONTAINER_NAME" sh -c "tr '\\0' '\\n' < /proc/1/environ | grep -q '^WORDPRESS_DB_HOST=localhost:3306$'"; then
  if ! grep -Eq "No 'wp-config.php' found.*WORDPRESS_DB_HOST" <<<"$logs"; then
    echo "Railway MYSQL* variables were not mapped into the WordPress runtime environment." >&2
    exit 1
  fi
fi

curl -fsS "http://localhost:${HOST_PORT}/" >/dev/null || true

cleanup

# Single-container SQLite mode: a fresh boot must install WordPress, seed
# content, and serve HTTP 200 from the redesigned site. This guards against
# the entrypoint placing the SQLite database outside the persistent uploads
# volume or leaving it owned by root with permissions www-data cannot write.
SQLITE_HOST_PORT="${SQLITE_HOST_PORT:-18081}"
SQLITE_VOLUME="${SQLITE_VOLUME:-catholic-kameari-smoke-uploads}"
docker volume rm "$SQLITE_VOLUME" >/dev/null 2>&1 || true
docker volume create "$SQLITE_VOLUME" >/dev/null

sqlite_cleanup() {
  docker rm -f "$CONTAINER_NAME" >/dev/null 2>&1 || true
  docker volume rm "$SQLITE_VOLUME" >/dev/null 2>&1 || true
}

trap sqlite_cleanup EXIT

docker run -d \
  --name "$CONTAINER_NAME" \
  -e WP_URL="http://localhost:${SQLITE_HOST_PORT}" \
  -e WP_ADMIN_USER=smoke \
  -e WP_ADMIN_PASSWORD=smoke-test-password \
  -e WP_ADMIN_EMAIL=smoke@example.com \
  -v "${SQLITE_VOLUME}:/var/www/html/wp-content/uploads" \
  -p "${SQLITE_HOST_PORT}:80" \
  "$IMAGE_NAME" >/dev/null

# Wait for the install + seed to finish and the site to start serving 200.
status=000
for _ in $(seq 1 60); do
  status="$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:${SQLITE_HOST_PORT}/" || echo 000)"
  if [ "$status" = "200" ]; then
    break
  fi
  sleep 2
done

if [ "$status" != "200" ]; then
  echo "SQLite-mode boot failed (status ${status})." >&2
  docker logs "$CONTAINER_NAME" 2>&1 | tail -50 >&2
  exit 1
fi

# The SQLite database must live inside the persistent uploads volume so a
# Railway redeploy does not wipe imported content.
if ! docker exec "$CONTAINER_NAME" test -f /var/www/html/wp-content/uploads/database/.ht.sqlite; then
  echo "SQLite database is not located under the persistent uploads volume." >&2
  docker exec "$CONTAINER_NAME" find /var/www/html/wp-content -name '*.sqlite*' >&2 || true
  exit 1
fi

# The legacy default location must NOT contain the live database, otherwise
# a redeploy would wipe content.
if docker exec "$CONTAINER_NAME" test -f /var/www/html/wp-content/database/.ht.sqlite; then
  echo "SQLite database leaked into the ephemeral wp-content/database/ path." >&2
  exit 1
fi

echo "Docker smoke test passed."
