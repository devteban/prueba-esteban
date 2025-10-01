#!/bin/bash

echo "🛑 Stopping Mini-Trello..."

# Stop Vite if running
if [ -f ".vite.pid" ]; then
    VITE_PID=$(cat .vite.pid)
    kill $VITE_PID 2>/dev/null && echo "✅ Vite stopped"
    rm .vite.pid
fi

# Stop containers
./vendor/bin/sail down

echo "✅ Everything stopped successfully"
