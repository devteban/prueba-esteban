#!/bin/bash

set -e

echo "🚀 Starting Mini-Trello in PRODUCTION mode..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Error: Docker is not running. Please start Docker first."
    exit 1
fi

# Clean up previous containers if they exist
echo "🧹 Cleaning up previous containers..."
docker-compose -f docker-compose.prod.yml down > /dev/null 2>&1 || true

# Build and start containers
echo "📦 Building containers for production..."
if ! docker-compose -f docker-compose.prod.yml up -d --build; then
    echo "❌ Error: Container build failed."
    exit 1
fi

# Wait for containers to be ready
echo "⏳ Waiting for services to be ready..."
for i in {1..30}; do
    if docker-compose -f docker-compose.prod.yml exec -T app php --version > /dev/null 2>&1; then
        echo "✅ Containers are ready!"
        break
    fi
    if [ $i -eq 30 ]; then
        echo "❌ Error: Containers are not responding after 30 attempts."
        docker-compose -f docker-compose.prod.yml ps
        exit 1
    fi
    echo "   Attempt $i/30..."
    sleep 2
done

# Install PHP dependencies for production
echo "📚 Installing production dependencies..."
if ! docker-compose -f docker-compose.prod.yml exec -T app composer install --no-dev --optimize-autoloader; then
    echo "❌ Error: Composer dependency installation failed."
    exit 1
fi

# Configure Laravel
echo "🔧 Configuring Laravel for production..."

# Configure environment variables for production BEFORE any cache
docker-compose -f docker-compose.prod.yml exec -T app sed -i 's/APP_ENV=local/APP_ENV=production/' .env
docker-compose -f docker-compose.prod.yml exec -T app sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env

if ! docker-compose -f docker-compose.prod.yml exec -T app php artisan key:generate --force; then
    echo "❌ Error: Application key generation failed."
    exit 1
fi

# Configure database
echo "🗄️ Setting up database..."
docker-compose -f docker-compose.prod.yml exec -T app mkdir -p database
docker-compose -f docker-compose.prod.yml exec -T app touch database/database.sqlite

if ! docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate:fresh --seed --force; then
    echo "❌ Error: Database configuration failed."
    exit 1
fi

# Build assets for production
echo "🎨 Compiling assets for production..."
if ! docker-compose -f docker-compose.prod.yml exec -T app npm install; then
    echo "❌ Error: Node.js dependency installation failed."
    exit 1
fi

# Remove 'hot' file if exists (indicates Vite dev mode)
docker-compose -f docker-compose.prod.yml exec -T app rm -f public/hot

if ! docker-compose -f docker-compose.prod.yml exec -T app npm run build; then
    echo "❌ Error: Asset compilation failed."
    exit 1
fi

# Optimize for production
echo "⚡ Optimizing for production..."
# Clear caches before regenerating them
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:clear > /dev/null 2>&1 || true
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:clear > /dev/null 2>&1 || true
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:clear > /dev/null 2>&1 || true

# Generate optimized caches
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:cache

# Small pause to ensure everything is stable
sleep 2

# Verify everything is correctly configured
echo "🔍 Verifying configuration..."
VERIFICATION=$(docker-compose -f docker-compose.prod.yml exec -T app php artisan tinker --execute="echo 'USERS:' . App\Models\User::count() . '|TASKS:' . App\Models\Task::count();" 2>&1 | grep -o 'USERS:[0-9]*|TASKS:[0-9]*')

if [ -n "$VERIFICATION" ]; then
    USER_COUNT=$(echo "$VERIFICATION" | grep -o 'USERS:[0-9]*' | grep -o '[0-9]*')
    TASK_COUNT=$(echo "$VERIFICATION" | grep -o 'TASKS:[0-9]*' | grep -o '[0-9]*')
    echo "✅ Database configured: $USER_COUNT user(s), $TASK_COUNT task(s)"
else
    echo "⚠️  Could not verify database, but configuration continued."
fi

echo ""
echo "✅ Mini-Trello is ready for PRODUCTION!"
echo ""
echo "🌐 Access the application at: http://localhost"
echo ""
echo "👤 Test users:"
echo "   Admin: admin@example.com / password"
echo ""
echo "🛠️  Useful production commands:"
echo "   docker-compose -f docker-compose.prod.yml logs -f                    # View logs"
echo "   docker-compose -f docker-compose.prod.yml down                       # Stop all"
echo "   docker-compose -f docker-compose.prod.yml exec app php artisan tinker # Laravel Tinker"
echo ""
echo "🚀 Production mode: Optimized assets and caches enabled!"
echo ""
