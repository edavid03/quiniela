#!/usr/bin/env bash
set -e

cd /var/www/html

mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
  echo "WARNING: APP_KEY no esta seteada. Genero una efimera, pero CAMBIARA en cada"
  echo "         deploy/reinicio e invalidara todas las sesiones (errores 419 al loguearse)."
  echo "         Sete APP_KEY como variable de entorno fija en Dokploy."
  export APP_KEY="$(php artisan key:generate --show --no-interaction)"
fi

if [ -n "${DB_HOST:-}" ]; then
  echo "Waiting for database ${DB_HOST}:${DB_PORT:-3306}..."
  until php -r '$host=getenv("DB_HOST"); $port=(int)(getenv("DB_PORT") ?: 3306); $socket=@fsockopen($host, $port, $errno, $errstr, 3); if (!$socket) { exit(1); } fclose($socket);'; do
    sleep 2
  done
fi

php artisan storage:link --force --no-interaction || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

# Seed opcional de cuentas demo. Solo DemoSeeder (idempotente, no destructivo);
# NUNCA el DatabaseSeeder completo, que borraria partidos/equipos.
if [ "${RUN_SEED:-false}" = "true" ]; then
  php artisan db:seed --class=DemoSeeder --force --no-interaction
fi

if [ "${APP_ENV:-production}" = "production" ]; then
  php artisan optimize:clear --no-interaction
  php artisan optimize --no-interaction
else
  php artisan config:clear --no-interaction
fi

exec "$@"
