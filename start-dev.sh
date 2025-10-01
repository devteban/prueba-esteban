#!/bin/bash

echo "🚀 Starting Mini-Trello in DEVELOPMENT mode..."
echo "🛑 To stop everything: ./stop-dev.sh"
echo ""

# Check if Sail is available
if [ ! -f "./vendor/bin/sail" ]; then
    echo "❌ Sail not found. Installing dependencies..."
    composer install
    npm install
fi

# Start Sail
echo "🐳 Starting containers..."
./vendor/bin/sail up -d --remove-orphans

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 5

# Check if database exists
if [ ! -f "database/database.sqlite" ]; then
    echo "🗄️  Setting up database..."
    ./vendor/bin/sail artisan migrate:fresh --seed
fi

# Start Vite in background
echo "🔥 Starting Vite (hot reload)..."
./vendor/bin/sail npm run dev > /dev/null 2>&1 &
VITE_PID=$!

# Save Vite PID to stop it later
echo $VITE_PID > .vite.pid

echo ""
echo "✅ Mini-Trello is fully ready!"
echo ""
echo "🌐 Application: http://localhost"
echo ""
echo "�� Credentials:"
echo "   Email: admin@example.com"
echo "   Password: password"
echo ""
echo "🛑 To stop everything:"
echo "   ./vendor/bin/sail down"
echo "   kill \$(cat .vite.pid) 2>/dev/null"
echo ""
