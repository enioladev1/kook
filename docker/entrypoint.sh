#!/bin/sh
set -e

php artisan config:cache
php artisan route:cache
php artisan view:cache

# --isolated: safe even if a redeploy briefly overlaps with the old
# container still shutting down, or multiple replicas start together.
php artisan migrate --force --isolated

php artisan db:seed --force --class=ProviderSeeder

exec "$@"
