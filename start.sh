#!/bin/bash

# Exchange Rate Hub - Quick Start Script
# This script helps you get started with the project quickly

set -e  # Exit on error

echo "============================================"
echo "Exchange Rate Hub - Quick Start"
echo "============================================"
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed!"
    echo "Please install Docker Desktop from: https://www.docker.com/products/docker-desktop/"
    exit 1
fi

# Check if Docker is running
if ! docker info &> /dev/null; then
    echo "❌ Docker is not running!"
    echo "Please start Docker Desktop and try again."
    exit 1
fi

echo "✅ Docker is installed and running"
echo ""

# Check if .env exists, if not create from example
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        echo "📝 Creating .env file from .env.example..."
        cp .env.example .env
        echo "✅ .env file created"
    else
        echo "⚠️  .env.example not found, using default values"
    fi
else
    echo "✅ .env file already exists"
fi
echo ""

# Navigate to docker directory
cd docker

echo "🚀 Starting Docker containers..."
echo "This may take a few minutes on first run..."
echo ""

# Start Docker containers
if docker compose up -d; then
    echo ""
    echo "============================================"
    echo "✅ Success! WordPress is starting up..."
    echo "============================================"
    echo ""
    echo "📍 WordPress URL: http://localhost:8000"
    echo ""
    echo "Next steps:"
    echo "1. Wait 10-15 seconds for WordPress to fully start"
    echo "2. Open http://localhost:8000 in your browser"
    echo "3. Complete the WordPress installation wizard"
    echo "4. Activate the 'Exchange Rate Hub' plugin"
    echo "5. Configure the plugin in 'Exchange Rates' menu"
    echo ""
    echo "💡 Useful commands:"
    echo "   View logs:    docker compose logs -f"
    echo "   Stop:         docker compose stop"
    echo "   Restart:      docker compose restart"
    echo "   Fresh start:  docker compose down -v && docker compose up -d"
    echo ""
    echo "📖 For detailed setup instructions, see LOCAL_SETUP.md"
    echo "============================================"
else
    echo ""
    echo "❌ Failed to start Docker containers"
    echo "Please check the error messages above"
    exit 1
fi
