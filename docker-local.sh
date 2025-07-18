#!/bin/bash

# Check if .env exists, if not create from .env.example
if [ ! -f ".env" ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    
    echo ".env file created. Please edit it with your local development values."
    echo "Run this script again after editing the .env file."
    exit 0
else
    echo ".env file exists, continuing with setup..."
fi

# Create storage directories if they don't exist
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Set correct permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Check which Docker Compose command is available
DOCKER_COMPOSE_CMD="docker compose"
if ! command -v docker &> /dev/null; then
    echo "Error: Docker is not installed. Please install Docker first."
    exit 1
fi

# Try the new command format first (docker compose)
if docker compose version &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
# Then try the old command format (docker-compose)
elif command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker-compose"
else
    echo "Error: Docker Compose is not installed. Please install Docker Compose first."
    echo "You can install it with: "
    echo "  apt-get update && apt-get install -y docker-compose-plugin"
    echo "  or"
    echo "  apt-get update && apt-get install -y docker-compose"
    exit 1
fi

# Build and start the Docker services
echo "Building and starting Docker services for local development..."
$DOCKER_COMPOSE_CMD -f docker-compose.local.yml up -d --build

echo "Docker setup complete!"
echo "You can access your application at: http://localhost"
echo ""
echo "To stop the containers, run: $DOCKER_COMPOSE_CMD -f docker-compose.local.yml down" 