#!/usr/bin/env bash
#
# Deploy the library. Run on the server, normally by the CI workflow.
#
# Only ever touches /opt/library. Anything else on the machine — including the
# weather station that shares this host — is left alone.

set -euo pipefail

APP_DIR="${APP_DIR:-/opt/library}"
HEALTH_URL="${HEALTH_URL:-http://127.0.0.1:8090/up}"

cd "$APP_DIR"

echo "── Fetching ──"
git fetch --quiet origin
git reset --quiet --hard origin/main
echo "   $(git log --oneline -1)"

echo "── Building ──"
docker compose build library

echo "── Starting ──"
# `up -d` rather than `restart`: a restart keeps the environment the container
# was created with, so .env changes would be ignored.
docker compose up -d

echo "── Waiting for health ──"
for attempt in $(seq 1 30); do
    if curl -fsS --max-time 5 "$HEALTH_URL" >/dev/null 2>&1; then
        echo "   healthy after ${attempt} attempt(s)"
        break
    fi

    if [ "$attempt" -eq 30 ]; then
        echo "!! The application did not come up. Recent log:" >&2
        docker compose logs --tail 40 library >&2
        exit 1
    fi

    sleep 3
done

echo "── Verifying migrations ──"
# The entrypoint runs them; this fails the deploy if any did not apply.
if docker compose exec -T library php artisan migrate:status 2>/dev/null | grep -q "Pending"; then
    echo "!! Migrations are still pending after startup." >&2
    docker compose exec -T library php artisan migrate:status >&2
    exit 1
fi

echo "── Done ──"
docker compose ps --format '   {{.Name}}  {{.Status}}'
