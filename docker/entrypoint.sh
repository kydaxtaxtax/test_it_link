#!/bin/bash
set -e

echo "Waiting for postgres to be ready..."
until PGPASSWORD=postgres psql -h postgres -U postgres -d car_ad_db -c '\q' 2>/dev/null; do
    sleep 1
done
echo "Postgres is ready!"

echo "Running migrations..."
php yii migrate/up --interactive=0

echo "Starting application..."
exec "$@"