#!/bin/bash
set -e

# ============================================
# INPUT VALIDATION
# ============================================

if [ "$1" = "" ]; then
  echo "ERROR: Please set name of the branch"
  exit 1
fi

# APP_KEY ($2) is intentionally optional now: apps that do not use Laravel's
# encrypted env feature (.env.encrypted) deploy without one. The orchestrator
# enforces presence/absence based on the per-app `requires_app_key` flag.

if [ "$3" = "" ]; then
  echo "ERROR: Please set the github username"
  exit 1
fi

if [ "$4" = "" ]; then
  echo "ERROR: Please set the github token"
  exit 1
fi

BRANCH="$1"
APP_KEY="${2:-}"
GITHUB_USER="$3"
GITHUB_TOKEN="$4"

# Ensure credentials are wiped from process environment on any exit path
# (success, failure, or interrupt). The askpass file is also removed.
ASKPASS_FILE=""
cleanup_credentials() {
  if [ -n "$ASKPASS_FILE" ] && [ -f "$ASKPASS_FILE" ]; then
    rm -f "$ASKPASS_FILE"
  fi
  unset GIT_USERNAME GIT_PASSWORD GITHUB_TOKEN APP_KEY
}
trap cleanup_credentials EXIT

# ============================================
# SYSTEM REQUIREMENTS VALIDATION
# ============================================

echo "=========================================="
echo "  Checking System Requirements"
echo "=========================================="

if ! command -v php &> /dev/null; then
  echo "❌ ERROR: PHP not found. Please install PHP first."
  exit 1
fi
echo "✅ PHP found: $(php -v | head -n 1)"

if ! command -v composer &> /dev/null; then
  echo "❌ ERROR: Composer not found. Please install Composer first."
  exit 1
fi
echo "✅ Composer found: $(composer --version)"

if ! command -v git &> /dev/null; then
  echo "❌ ERROR: Git not found. Please install Git first."
  exit 1
fi
echo "✅ Git found: $(git --version)"

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

if [ -f ".env.encrypted" ]; then
  # Encrypted env was committed: APP_KEY is mandatory to decrypt it.
  if [ -z "$APP_KEY" ]; then
    echo "❌ ERROR: .env.encrypted exists but no APP_KEY was supplied."
    echo "         Either remove .env.encrypted from the repository or"
    echo "         enable 'requires_app_key' for this app in the orchestrator."
    exit 1
  fi

  echo "🔓 Decrypting environment file..."
  # Suppress stdout: env:decrypt prints the encryption key in cleartext.
  if ! php artisan env:decrypt --key="$APP_KEY" >/dev/null 2>&1; then
    echo "❌ Failed to decrypt environment file"
    exit 1
  fi
  rm -f .env.encrypted
  echo "✅ Environment file decrypted"
else
  # No encrypted env shipped — use whatever .env is on disk, or seed from
  # .env.example as a last resort. We deliberately do NOT inject APP_KEY into
  # a generated .env: if the app does not use encrypted env, its APP_KEY is
  # whatever .env.example / .env defines, not the orchestrator-supplied value.
  echo "ℹ️  No encrypted environment file found"
  if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
      echo "📝 Seeding .env from .env.example..."
      cp .env.example .env
    else
      echo "❌ ERROR: Neither .env nor .env.example present in the repository."
      exit 1
    fi
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
php artisan down --no-interaction

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

if [ "$REPO_URL" = "" ]; then
  echo "❌ ERROR: REPO_URL environment variable is not set"
  exit 1
fi

# Use GIT_ASKPASS so credentials never appear in:
#   - argv of git (visible via `ps aux`)
#   - the URL stored in .git/config
#   - any process logs or shell history
# The askpass helper reads from process-private environment variables.
export GIT_USERNAME="$GITHUB_USER"
export GIT_PASSWORD="$GITHUB_TOKEN"

ASKPASS_FILE=$(mktemp)
cat > "$ASKPASS_FILE" << 'EOF'
#!/bin/bash
case "$1" in
  Username*) echo "$GIT_USERNAME" ;;
  Password*) echo "$GIT_PASSWORD" ;;
esac
EOF
chmod 700 "$ASKPASS_FILE"

# Reset and refresh: fetch from origin, hard-reset to the requested branch.
# This produces consistent state across nodes (no merge surprises).
git reset --hard

GIT_ASKPASS="$ASKPASS_FILE" GIT_TERMINAL_PROMPT=0 \
  git -c http.sslVerify=false fetch "$REPO_URL" "$BRANCH"

git reset --hard FETCH_HEAD
git clean -fd

# Cleanup credentials immediately after git is done with them.
rm -f "$ASKPASS_FILE"
ASKPASS_FILE=""
unset GIT_USERNAME GIT_PASSWORD

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
if command -v systemctl &> /dev/null; then
  if systemctl --user list-units --type=service | grep -q "php.*fpm"; then
    echo "Restarting PHP-FPM (user-level)..."
    systemctl --user restart php*-php-fpm 2>/dev/null || echo "⚠️  Could not restart PHP-FPM at user level"
  fi
fi

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

if [ -n "$APP_KEY" ]; then
  echo "🔒 Encrypting environment file..."
  # Suppress stdout: env:encrypt prints the encryption key in cleartext.
  if ! php artisan env:encrypt --key="$APP_KEY" >/dev/null 2>&1; then
    echo "❌ Failed to encrypt environment file"
    exit 1
  fi
  rm -f .env
  echo "✅ Environment file encrypted"
else
  echo "ℹ️  Skipping env:encrypt (no APP_KEY supplied — app does not use encrypted env)"
fi

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
if [ -d "public" ]; then
  find public -type d -exec chmod 775 {} \;
  find public -type f -exec chmod 664 {} \;
  echo "✅ Public directory permissions set"
fi

if [ -d "storage" ]; then
  find storage -type d -exec chmod 775 {} \;
  find storage -type f -exec chmod 664 {} \;
  echo "✅ Storage directory permissions set"
fi

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

echo "📊 Final Status:"
echo "✅ Application is live"
echo "✅ Dependencies installed"
echo "✅ Cache optimized"
echo "✅ Database migrated"
echo "✅ Queues restarted"
if [ -n "$APP_KEY" ]; then
  echo "✅ Environment secured"
fi
echo "✅ File permissions set"
echo ""

echo "🔍 Laravel Version:"
php artisan --version

echo ""
echo "🎉 Deployment completed successfully!"
echo "=========================================="
