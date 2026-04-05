#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
  application/config \
  application/modules \
  writable/install \
  writable/cache/data \
  writable/logs \
  writable/backups \
  writable/uploads/captcha \
  writable/uploads/news \
  writable/uploads/avatar \
  writable/uploads/modules

chmod -R 0777 application/config application/modules writable || true

exec "$@"
