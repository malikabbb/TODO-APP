#!/bin/bash
set -e # exit on first error

# ============================================
# INPUT VALIDATION step 1 - test-test-now
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
# ENVIRONMENT SETUP (FIRST)
# ============================================

echo ""
echo "=========================================="
echo "  Setting Up Environment"
echo "=========================================="

# Decrypt environment file first
if [ -f ".env.encrypted" ]; then
  echo "🔓 Decrypting environment file..."
  php artisan env:decrypt --key=$APP_KEY
  rm .env.encrypted
  echo "✅ Environment file decrypted"
else
  echo "⚠️  No encrypted environment file found"
  # Create basic .env if not exists
  if [ ! -f ".env" ]; then
    echo "📝 Creating basic .env file..."
    cp .env.example .env 2>/dev/null || echo "APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=$APP_KEY\nAPP_DEBUG=true\nAPP_URL=http://localhost" > .env
  fi
fi

# ============================================
# DEPENDENCIES INSTALLATION (SECOND)
# ============================================

echo ""
echo "=========================================="
echo "  Installing Dependencies"
echo "=========================================="

echo "📦 Installing Composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "✅ Dependencies installed successfully"

# ============================================
# MAINTENANCE MODE (THIRD)
# ============================================

echo ""
echo "=========================================="
echo "  Enabling Maintenance Mode"
echo "=========================================="

echo "🔧 Enabling maintenance mode..."
php artisan down --no-interaction --secret="VXhJrHdStlMKsqvuOokdPJ"

# ============================================
# GIT OPERATIONS (FOURTH)
# ============================================

echo ""
echo "=========================================="
echo "  Updating Repository"
echo "=========================================="

echo "📥 Updating repository..."
echo "Repository: $REPO_URL"
echo "Branch: $BRANCH"

# Validate REPO_URL
if [ "$REPO_URL" = "" ]; then
  echo "❌ ERROR: REPO_URL environment variable is not set"
  exit 1
fi

# Fix Git URL - remove duplicate https:// and add credentials
if [[ "$REPO_URL" == *"https://"* ]]; then
  # Remove https:// from REPO_URL and add it with credentials
  CLEAN_REPO_URL=$(echo "$REPO_URL" | sed 's|https://||')
  GIT_REPO_URL="https://$GITHUB_USER:$GITHUB_TOKEN@$CLEAN_REPO_URL"
elif [[ "$REPO_URL" == *"github.com"* ]]; then
  GIT_REPO_URL="https://$GITHUB_USER:$GITHUB_TOKEN@$REPO_URL"
else
  # If REPO_URL doesn't include github.com, construct it
  GIT_REPO_URL="https://$GITHUB_USER:$GITHUB_TOKEN@github.com/$REPO_URL"
fi

echo "Git URL: $GIT_REPO_URL"

# Reset any local changes
git reset --hard

# Pull latest changes
git -c http.sslVerify=false pull $GIT_REPO_URL $BRANCH

echo "✅ Repository updated"

# ============================================
# LARAVEL CACHE OPTIMIZATIONS
# ============================================

echo ""
echo "=========================================="
echo "  Optimizing Laravel Cache"
echo "=========================================="

echo "⚡ Optimizing Laravel cache..."
php artisan route:cache
php artisan config:cache
php artisan view:cache

echo "✅ Cache optimized"

# ============================================
# DATABASE OPERATIONS
# ============================================

echo ""
echo "=========================================="
echo "  Database Operations"
echo "=========================================="

echo "🗄️  Running database migrations..."
php artisan migrate --force

echo "✅ Database migrations completed"

# ============================================
# QUEUE OPERATIONS
# ============================================

echo ""
echo "=========================================="
echo "  Queue Operations"
echo "=========================================="

echo "🔄 Restarting queues..."
php artisan queue:restart

echo "✅ Queues restarted"

# ============================================
# APPLICATION RELOAD
# ============================================

echo ""
echo "=========================================="
echo "  Application Reload"
echo "=========================================="

echo "🚀 Reloading application..."
if php artisan list | grep -q "octane"; then
  php artisan octane:reload
  echo "✅ Application reloaded via Octane"
else
  echo "ℹ️  Octane not installed - skipping reload"
fi

# ============================================
# SERVICE MANAGEMENT (User-level)
# ============================================

echo ""
echo "=========================================="
echo "  Service Management"
echo "=========================================="

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

echo "✅ Service management completed"

# ============================================
# ENVIRONMENT CLEANUP
# ============================================

echo ""
echo "=========================================="
echo "  Securing Environment"
echo "=========================================="

echo "🔒 Encrypting environment file..."
# Encrypt environment file again
php artisan env:encrypt --key=$APP_KEY
rm .env
echo "✅ Environment file encrypted"

# ============================================
# MAINTENANCE MODE OFF
# ============================================

echo ""
echo "=========================================="
echo "  Disabling Maintenance Mode"
echo "=========================================="

echo "🟢 Disabling maintenance mode..."
php artisan up

echo "✅ Application is live"

# ============================================
# POST-DEPLOYMENT TASKS
# ============================================

echo ""
echo "=========================================="
echo "  Post-Deployment Tasks"
echo "=========================================="

echo "🌐 Running post-deployment tasks..."
# Reload Cloudflare IPs if command exists
if php artisan list | grep -q "cloudflare"; then
  php artisan cloudflare:reload
  echo "✅ Cloudflare IPs reloaded"
else
  echo "ℹ️  Cloudflare not configured - skipping"
fi

# ============================================
# FILE PERMISSIONS (User-level)
# ============================================

echo ""
echo "=========================================="
echo "  Setting File Permissions"
echo "=========================================="

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

# Set permissions for bootstrap cache
if [ -d "bootstrap/cache" ]; then
  find bootstrap/cache -type d -exec chmod 775 {} \;
  find bootstrap/cache -type f -exec chmod 664 {} \;
  echo "✅ Bootstrap cache permissions set"
fi

# ============================================
# DEPLOYMENT COMPLETION
# ============================================

echo ""
echo "=========================================="
echo "  Laravel Deployment Completed Successfully!"
echo "=========================================="
echo "Branch: $BRANCH"
echo "Repository: $REPO_URL"
echo "Finished at: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# Show final status
echo "📊 Final Status:"
echo "✅ Application is live"
echo "✅ Dependencies installed"
echo "✅ Cache optimized"
echo "✅ Database migrated"
echo "✅ Queues restarted"
echo "✅ Environment secured"
echo "✅ File permissions set"
echo ""

# Show Laravel version
echo "🔍 Laravel Version:"
php artisan --version

echo ""
echo "🎉 Deployment completed successfully!"
echo "=========================================="
