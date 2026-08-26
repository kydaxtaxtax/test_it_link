#!/bin/bash
set -e

echo "Waiting for postgres to be ready..."
for i in {1..30}; do
    if timeout 3 bash -c "nc -z postgres 5432" 2>/dev/null; then
        echo "Postgres is ready!"
        break
    fi
    echo "Attempt $i failed, retrying..."
    sleep 2
done

echo "Running migrations..."
php yii migrate/up --interactive=0

echo "Starting application..."
exec "$@"