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
  -e WORDPRESS_DB_HOST=localhost \
  -e WORDPRESS_DB_NAME=wordpress \
  -e WORDPRESS_DB_USER=wordpress \
  -e WORDPRESS_DB_PASSWORD=wordpress \
  "$IMAGE_NAME" \
  apache2ctl -t

cleanup

docker run -d \
  --name "$CONTAINER_NAME" \
  -e PORT=8080 \
  -e WORDPRESS_DB_HOST=localhost \
  -e WORDPRESS_DB_NAME=wordpress \
  -e WORDPRESS_DB_USER=wordpress \
  -e WORDPRESS_DB_PASSWORD=wordpress \
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

curl -fsS "http://localhost:${HOST_PORT}/" >/dev/null || true

echo "Docker smoke test passed."

