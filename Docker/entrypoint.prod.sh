#!/bin/sh
# Production container entrypoint (Railway).
# Railway containers are ephemeral: nothing written to disk outside a mounted
# volume survives a redeploy. This script rebuilds what's needed at boot time
# from env vars, then hands off to Apache.
set -e

# Railway assigns the public HTTP port dynamically via $PORT; Apache's default
# config listens on 80, so point it at the right port before starting.
if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then
  sed -ri "s/^Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/*.conf
fi

# The JWT keypair isn't part of the image (it's gitignored) and Railway has no
# persistent disk here unless a volume is mounted at config/jwt — so rebuild
# it from base64 env vars on every boot instead.
if [ -n "$JWT_PRIVATE_KEY_B64" ] && [ -n "$JWT_PUBLIC_KEY_B64" ]; then
  mkdir -p config/jwt
  echo "$JWT_PRIVATE_KEY_B64" | base64 -d > config/jwt/private.pem
  echo "$JWT_PUBLIC_KEY_B64" | base64 -d > config/jwt/public.pem
  chmod 600 config/jwt/private.pem
fi

# public/uploads should be a mounted volume in production so images survive
# redeploys; make sure the directory exists even on the very first boot.
mkdir -p public/uploads/listings var
chown -R www-data:www-data public/uploads var config/jwt 2>/dev/null || true

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod

# Defensive: a stale PID file left over from a previous crashed run on the
# same container/volume can make Apache think it's already running and
# misbehave on start.
rm -f /var/run/apache2/apache2.pid

# mpm_event stays enabled alongside mpm_prefork on the actual Railway
# runtime no matter what the image build does to mods-enabled/ (confirmed
# via `railway ssh`: removing the symlinks in the Dockerfile RUN step does
# NOT persist to the running container, for reasons that don't fully make
# sense given other build-time fixes in the same image do persist) --
# Apache refuses to start with two MPMs loaded, so remove it here too, at
# actual container boot, right before Apache starts.
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf

exec "$@"
