#!/usr/bin/env bash

set -Eeuo pipefail
umask 027

readonly APP_ROOT=/var/www/primo
readonly RELEASES_DIR="$APP_ROOT/releases"
readonly SHARED_DIR="$APP_ROOT/shared"
readonly CURRENT_LINK="$APP_ROOT/current"
readonly BACKUP_DIR=/var/backups/primo/mysql
readonly LOG_FILE=/var/log/primo-deploy.log
readonly REPOSITORY_URL=https://github.com/tperdue/primo_website.git

if [[ $# -ne 1 || ! $1 =~ ^[0-9a-f]{40}$ ]]; then
    echo 'Usage: deploy-production.sh <40-character commit SHA>' >&2
    exit 2
fi

readonly COMMIT_SHA=$1
readonly RELEASE_ID="$(date -u +%Y%m%dT%H%M%SZ)-${COMMIT_SHA:0:12}"
readonly RELEASE_DIR="$RELEASES_DIR/$RELEASE_ID"
readonly NEXT_LINK="$APP_ROOT/.current-$RELEASE_ID"

exec 9>/run/lock/primo-deploy.lock
if ! flock -n 9; then
    echo 'Another Primo deployment is already running.' >&2
    exit 3
fi

touch "$LOG_FILE"
chown root:adm "$LOG_FILE"
chmod 0640 "$LOG_FILE"
exec > >(tee -a "$LOG_FILE") 2>&1

echo "[$(date -u +%FT%TZ)] Deploying $COMMIT_SHA"

if [[ -e "$RELEASE_DIR" || -L "$NEXT_LINK" ]]; then
    echo "Release path already exists: $RELEASE_DIR" >&2
    exit 4
fi

install -d -o primo -g www-data -m 2775 "$RELEASES_DIR"
install -d -o primo -g www-data -m 2775 "$SHARED_DIR/writable"
install -d -o primo -g www-data -m 2775 "$SHARED_DIR/public-uploads"
install -d -o root -g root -m 0700 "$BACKUP_DIR"

if [[ ! -f "$SHARED_DIR/.env" ]]; then
    echo "Missing shared environment file: $SHARED_DIR/.env" >&2
    exit 5
fi

sudo -u primo git clone --quiet --no-checkout "$REPOSITORY_URL" "$RELEASE_DIR"
sudo -u primo git -C "$RELEASE_DIR" checkout --quiet --detach "$COMMIT_SHA"

if [[ "$(sudo -u primo git -C "$RELEASE_DIR" rev-parse HEAD)" != "$COMMIT_SHA" ]]; then
    echo 'Checked-out commit does not match the requested commit.' >&2
    exit 6
fi

ln -s "$SHARED_DIR/.env" "$RELEASE_DIR/.env"

if [[ -d "$RELEASE_DIR/writable" ]]; then
    mv "$RELEASE_DIR/writable" "$RELEASE_DIR/writable.release-original"
fi
ln -s "$SHARED_DIR/writable" "$RELEASE_DIR/writable"

if [[ -f "$RELEASE_DIR/public/uploads/.htaccess" && ! -f "$SHARED_DIR/public-uploads/.htaccess" ]]; then
    install -o primo -g www-data -m 0644 "$RELEASE_DIR/public/uploads/.htaccess" "$SHARED_DIR/public-uploads/.htaccess"
fi
rm -rf -- "$RELEASE_DIR/public/uploads"
ln -s "$SHARED_DIR/public-uploads" "$RELEASE_DIR/public/uploads"

chown -R primo:www-data "$RELEASE_DIR"
sudo -u primo composer install \
    --working-dir="$RELEASE_DIR" \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --optimize-autoloader
sudo -u primo composer check-platform-reqs --no-dev --working-dir="$RELEASE_DIR"

database_name="$(awk -F= '$1 ~ /^[[:space:]]*database[.]default[.]database[[:space:]]*$/ { value=$2; sub(/^[[:space:]]*/, "", value); sub(/[[:space:]\r]*$/, "", value); gsub(/^\047|\047$/, "", value); gsub(/^\042|\042$/, "", value); print value; exit }' "$SHARED_DIR/.env")"
if [[ -z "$database_name" || "$database_name" == *[!A-Za-z0-9_]* ]]; then
    echo 'The configured database name is missing or unsafe.' >&2
    exit 7
fi

backup_file="$BACKUP_DIR/pre-deploy-$RELEASE_ID.sql.gz"
mysqldump --single-transaction --routines --events --triggers --databases "$database_name" | gzip -9 > "$backup_file"
chmod 0600 "$backup_file"
gzip -t "$backup_file"

sudo -u primo php "$RELEASE_DIR/spark" migrate --all

previous_release="$(readlink -f "$CURRENT_LINK" 2>/dev/null || true)"
ln -s "$RELEASE_DIR" "$NEXT_LINK"
mv -Tf "$NEXT_LINK" "$CURRENT_LINK"
systemctl reload php8.3-fpm

health_failed=0
for path in / /login; do
    if ! curl --fail --silent --show-error \
        --resolve primodemo.eastpointsoftware.net:443:127.0.0.1 \
        "https://primodemo.eastpointsoftware.net$path" >/dev/null; then
        health_failed=1
    fi
done

if [[ $health_failed -ne 0 ]]; then
    echo 'Post-deploy health checks failed.' >&2
    if [[ -n "$previous_release" && "$previous_release" == "$RELEASES_DIR/"* && -d "$previous_release" ]]; then
        rollback_link="$APP_ROOT/.rollback-$RELEASE_ID"
        ln -s "$previous_release" "$rollback_link"
        mv -Tf "$rollback_link" "$CURRENT_LINK"
        systemctl reload php8.3-fpm
        echo "Restored previous code release: $previous_release" >&2
        echo 'Database migrations were not reversed.' >&2
    fi
    exit 8
fi

bash -n "$RELEASE_DIR/deploy/deploy-production.sh"
bash -n "$RELEASE_DIR/deploy/primo-deploy-entrypoint.sh"
install -o root -g root -m 0755 "$RELEASE_DIR/deploy/deploy-production.sh" /usr/local/sbin/primo-deploy
install -o root -g root -m 0755 "$RELEASE_DIR/deploy/primo-deploy-entrypoint.sh" /usr/local/sbin/primo-deploy-entrypoint

echo "[$(date -u +%FT%TZ)] Deployment completed: $RELEASE_DIR"
