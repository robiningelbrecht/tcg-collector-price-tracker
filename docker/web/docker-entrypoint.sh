#!/bin/sh
set -e

echo "date.timezone=\"${TZ:-UTC}\"" > "${PHP_INI_DIR}/conf.d/timezone.ini"

crond -b -l 2

exec "$@"
