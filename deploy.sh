#!/usr/bin/env bash
# ============================================================
# deploy.sh — Zero-downtime production deployment script
# Run from the project root on your production server:
#   bash deploy.sh
# ============================================================
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
LOG_FILE="$APP_DIR/storage/logs/deploy_$TIMESTAMP.log"

echo "========================================"
echo " RecruitSmart Deployment — $TIMESTAMP"
echo "========================================"

# 1. Put application into maintenance mode
echo "→ Enabling maintenance mode..."
php artisan down --retry=60 2>&1 | tee -a "$LOG_FILE"

# 2. Pull latest code
echo "→ Pulling latest code from git..."
git pull origin main 2>&1 | tee -a "$LOG_FILE"

# 3. Install PHP dependencies
echo "→ Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist 2>&1 | tee -a "$LOG_FILE"

# 4. Install & build frontend assets
echo "→ Installing npm dependencies..."
npm ci 2>&1 | tee -a "$LOG_FILE"

echo "→ Building frontend assets..."
npm run build 2>&1 | tee -a "$LOG_FILE"

# 5. Run database migrations
echo "→ Running migrations..."
php artisan migrate --force 2>&1 | tee -a "$LOG_FILE"

# 6. Clear & rebuild caches
echo "→ Rebuilding caches..."
php artisan optimize:clear 2>&1 | tee -a "$LOG_FILE"
php artisan config:cache 2>&1 | tee -a "$LOG_FILE"
php artisan route:cache 2>&1 | tee -a "$LOG_FILE"
php artisan view:cache 2>&1 | tee -a "$LOG_FILE"

# 7. Restart queue workers
echo "→ Restarting queue workers..."
php artisan queue:restart 2>&1 | tee -a "$LOG_FILE"

# 8. Fix storage permissions
echo "→ Fixing storage permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# 9. Bring application back up
echo "→ Disabling maintenance mode..."
php artisan up 2>&1 | tee -a "$LOG_FILE"

echo ""
echo "✓ Deployment complete! Log: $LOG_FILE"
