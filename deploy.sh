#!/bin/bash
# ============================================================
# TODO APP - Laravel Deployment Script
# ============================================================
# This script is executed by Ansible after cloning the repository.
# Arguments:
#   $1 = branch
#   $2 = app_key (APP_KEY)
#   $3 = github_username
#   $4 = github_token
# ============================================================

set -e

echo "=========================================="
echo "  TODO APP - Deploy Script Started"
echo "=========================================="
echo "Branch: $1"
echo "Time: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# ----------------------------------------------------------
# Step 1: Setup Environment File
# ----------------------------------------------------------
echo "[1/8] Setting up environment file..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "  -> Created .env from .env.example"
else
    echo "  -> .env already exists, skipping"
fi

# Inject the APP_KEY
sed -i "s|^APP_KEY=.*|APP_KEY=$2|" .env
echo "  -> APP_KEY configured"

# ----------------------------------------------------------
# Step 2: Install Composer Dependencies
# ----------------------------------------------------------
echo ""
echo "[2/8] Installing Composer dependencies..."
composer install --no-interaction --no-dev --optimize-autoloader --quiet
echo "  -> Composer dependencies installed"

# ----------------------------------------------------------
# Step 3: Run Database Migrations
# ----------------------------------------------------------
echo ""
echo "[3/8] Running database migrations..."
php artisan migrate --force --no-interaction
echo "  -> Migrations completed"

# ----------------------------------------------------------
# Step 4: Cache Configuration
# ----------------------------------------------------------
echo ""
echo "[4/8] Caching configuration..."
php artisan config:cache
echo "  -> Config cached"

# ----------------------------------------------------------
# Step 5: Cache Routes
# ----------------------------------------------------------
echo ""
echo "[5/8] Caching routes..."
php artisan route:cache
echo "  -> Routes cached"

# ----------------------------------------------------------
# Step 6: Cache Views
# ----------------------------------------------------------
echo ""
echo "[6/8] Caching views..."
php artisan view:cache
echo "  -> Views cached"

# ----------------------------------------------------------
# Step 7: Fix File Permissions
# ----------------------------------------------------------
echo ""
echo "[7/8] Fixing file permissions..."
chmod -R 775 storage bootstrap/cache
echo "  -> Permissions fixed"

# ----------------------------------------------------------
# Step 8: Restart Queue Workers (if any)
# ----------------------------------------------------------
echo ""
echo "[8/8] Restarting queue workers..."
php artisan queue:restart 2>/dev/null || echo "  -> No queue workers to restart"

echo ""
echo "=========================================="
echo "  TODO APP - Deployment Completed!"
echo "=========================================="
echo "Finished at: $(date '+%Y-%m-%d %H:%M:%S')"
