#!/bin/bash
set -e # exit on first error

# ============================================
# INPUT VALIDATION
# ============================================

if [ "$1" = "" ]; then
  echo "ERROR: Please set name of the branch"
  exit 1
fi

if [ "$2" = "" ]; then
  echo "ERROR: Please set the encryption key"
  exit 1
fi

if [ "$3" = "" ]; then
  echo "ERROR: Please set the github username"
  exit 1
fi

if [ "$4" = "" ]; then
  echo "ERROR: Please set the github token"
  exit 1
fi

BRANCH="$1"
APP_KEY="$2"
GITHUB_USER="$3"
GITHUB_TOKEN="$4"

# ============================================
# SYSTEM REQUIREMENTS VALIDATION
# ============================================

echo "=========================================="
echo "  Checking System Requirements"
echo "=========================================="

# Check PHP
if ! command -v php &> /dev/null; then
  echo "❌ ERROR: PHP not found. Please install PHP first."
  exit 1
fi
echo "✅ PHP found: $(php -v | head -n 1)"

# Check Composer
if ! command -v composer &> /dev/null; then
  echo "❌ ERROR: Composer not found. Please install Composer first."
  exit 1
fi
echo "✅ Composer found: $(composer --version)"

# Check Git
if ! command -v git &> /dev/null; then
  echo "❌ ERROR: Git not found. Please install Git first."
  exit 1
fi
echo "✅ Git found: $(git --version)"

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
  echo "❌ ERROR: Not in a Laravel project (artisan file not found)"
  exit 1
fi
echo "✅ Laravel project detected"

# ============================================
# DEPLOYMENT START
# ============================================

echo ""
echo "=========================================="
echo "  Starting Laravel Deployment"
echo "=========================================="
echo "Branch: $BRANCH"
echo "Started at: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="

# ============================================
# MAINTENANCE MODE
# ============================================

echo "🔧 Enabling maintenance mode..."
php artisan down --no-interaction --render="errors.updating" --secret="VXhJrHdStlMKsqvuOokdPJ"

# ============================================
# GIT OPERATIONS
# ============================================

echo "📥 Updating repository..."
# Reset any local changes
git reset --hard

# Pull latest changes
git -c http.sslVerify=false pull https://$GITHUB_USER:$GITHUB_TOKEN@github.com/central-bank-libya/fcms.git $BRANCH

# ============================================
# ENVIRONMENT SETUP
# ============================================

echo "🔓 Setting up environment..."
# Decrypt environment file
if [ -f ".env.encrypted" ]; then
  php artisan env:decrypt --key=$APP_KEY
  rm .env.encrypted
  echo "✅ Environment file decrypted"
else
  echo "⚠️  No encrypted environment file found"
fi

# ============================================
# DEPENDENCIES
# ============================================

echo "📦 Installing dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# ============================================
# LARAVEL CACHE OPTIMIZATIONS
# ============================================

echo "⚡ Optimizing Laravel cache..."
php artisan route:cache
php artisan config:cache
php artisan view:cache

# ============================================
# QUEUE OPERATIONS
# ============================================

echo "🔄 Restarting queues..."
php artisan queue:restart

# ============================================
# APPLICATION RELOAD
# ============================================

echo "🚀 Reloading application..."
php artisan octane:reload

# ============================================
# SERVICE MANAGEMENT (User-level)
# ============================================

echo "🔧 Managing services..."
# Try user-level service restart first
if command -v systemctl &> /dev/null; then
  if systemctl --user list-units --type=service | grep -q "php.*fpm"; then
    echo "Restarting PHP-FPM (user-level)..."
    systemctl --user restart php*-php-fpm 2>/dev/null || echo "⚠️  Could not restart PHP-FPM at user level"
  fi
fi

# Alternative: Use Laravel commands to reload
php artisan config:cache
php artisan queue:restart

# ============================================
# ENVIRONMENT CLEANUP
# ============================================

echo "🔒 Securing environment..."
# Encrypt environment file again
php artisan env:encrypt --key=$APP_KEY
rm .env
echo "✅ Environment file encrypted"

# ============================================
# MAINTENANCE MODE OFF
# ============================================

echo "🟢 Disabling maintenance mode..."
php artisan up

# ============================================
# POST-DEPLOYMENT TASKS
# ============================================

echo "🌐 Running post-deployment tasks..."
# Reload Cloudflare IPs if command exists
if php artisan list | grep -q "cloudflare"; then
  php artisan cloudflare:reload
  echo "✅ Cloudflare IPs reloaded"
fi

# ============================================
# FILE PERMISSIONS (User-level)
# ============================================

echo "📁 Setting file permissions..."
# Set permissions for public directory (user-level)
if [ -d "public" ]; then
  find public -type d -exec chmod 775 {} \;
  find public -type f -exec chmod 664 {} \;
  echo "✅ Public directory permissions set"
fi

# Set permissions for storage directories
if [ -d "storage" ]; then
  find storage -type d -exec chmod 775 {} \;
  find storage -type f -exec chmod 664 {} \;
  echo "✅ Storage directory permissions set"
fi

# ============================================
# DEPLOYMENT COMPLETION
# ============================================

echo ""
echo "=========================================="
echo "  Laravel Deployment Completed Successfully!"
echo "=========================================="
echo "Branch: $BRANCH"
echo "Finished at: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# Show final status
echo "📊 Final Status:"
echo "✅ Application is live"
echo "✅ Cache optimized"
echo "✅ Queues restarted"
echo "✅ Environment secured"
echo ""

# Show Laravel version
echo "🔍 Laravel Version:"
php artisan --version

echo ""
echo "🎉 Deployment completed successfully!"
echo "=========================================="
