#!/usr/bin/env bash

set -e

echo "Clearing Laravel cache..."
php artisan optimize:clear

echo "Running migrations..."
php artisan migrate --force

echo "Seeding demo data..."
php artisan db:seed --class=SubmitMateDemoSeeder --force || true

echo "Creating storage link..."
php artisan storage:link || true

echo "Caching config..."
php artisan config:cache

echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-10000}