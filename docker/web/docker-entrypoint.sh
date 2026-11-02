#!/bin/sh
set -e

echo "date.timezone=\"${TZ:-UTC}\"" > "${PHP_INI_DIR}/conf.d/timezone.ini"

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

crond -b -l 2

exec "$@"
