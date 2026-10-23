#!/bin/bash
# Cloud Setup Script - Alternative to devcontainer postCreateCommand
# This script can be run manually or called from other CI/CD pipelines

set -e

echo "=== Cloud Setup for Trainer Desk ==="

# Step 1: Install Composer dependencies
echo "[1/5] Installing Composer dependencies..."
composer install --no-interaction --prefer-dist

# Step 2: Generate application key
echo "[2/5] Generating application key..."
php artisan key:generate --ansi

# Step 3: Initialize SQLite database
echo "[3/5] Initializing SQLite database..."
touch database/database.sqlite
php artisan migrate --force

# Step 4: Install npm dependencies and build assets
echo "[4/5] Installing npm dependencies and building assets..."
npm install
npm run build

# Step 5: Seed database with initial data
echo "[5/5] Seeding database with initial data..."
php artisan db:seed

echo ""
echo "✅ Cloud setup complete!"
echo ""
echo "Next steps:"
echo "  - Start development server: composer dev"
echo "  - Open http://localhost:8000 in your browser"
echo ""
