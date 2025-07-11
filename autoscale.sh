#!/bin/bash

SERVICE_NAME=admin-panel-app
THRESHOLD=70       # CPU % threshold to trigger scale-up
SCALE_STEP=1       # How many containers to add/remove
MAX_SCALE=5
MIN_SCALE=1

# Get current CPU usage of the service
CPU_USAGE=$(docker stats --no-stream --format "{{.CPUPerc}}" "$SERVICE_NAME" | cut -d'%' -f1 | cut -d'.' -f1)

# Get current replica count
CURRENT_SCALE=$(docker ps --filter "name=${SERVICE_NAME}" --format "{{.Names}}" | wc -l)

echo "🔍 CPU Usage: $CPU_USAGE%, Current Scale: $CURRENT_SCALE"

# Scale up
if [ "$CPU_USAGE" -gt "$THRESHOLD" ] && [ "$CURRENT_SCALE" -lt "$MAX_SCALE" ]; then
    NEW_SCALE=$((CURRENT_SCALE + SCALE_STEP))
    if [ "$NEW_SCALE" -gt "$MAX_SCALE" ]; then
        NEW_SCALE=$MAX_SCALE
    fi
    echo "🚀 High CPU usage detected. Scaling up to $NEW_SCALE..."
    docker compose up --scale "$SERVICE_NAME=$NEW_SCALE" -d

# Scale down
elif [ "$CPU_USAGE" -le "$THRESHOLD" ] && [ "$CURRENT_SCALE" -gt "$MIN_SCALE" ]; then
    NEW_SCALE=$((CURRENT_SCALE - SCALE_STEP))
    if [ "$NEW_SCALE" -lt "$MIN_SCALE" ]; then
        NEW_SCALE=$MIN_SCALE
    fi
    echo "📉 Low CPU usage. Scaling down to $NEW_SCALE..."
    docker compose up --scale "$SERVICE_NAME=$NEW_SCALE" -d

else
    echo "✅ No scaling action needed."
fi

docker stats --no-stream --format "{{.CPUPerc}}" "ws-app" | cut -d'%' -f1 | cut -d'.' -f1