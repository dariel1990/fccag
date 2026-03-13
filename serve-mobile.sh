#!/bin/bash

# Start Laravel development server on all interfaces
# This allows the mobile emulator to connect via your local IP

echo "Starting LCAS server for mobile testing..."
echo "Server will be available at: http://192.168.1.46:8000"
echo ""
echo "Make sure to update the API URL in your mobile app to:"
echo "  http://192.168.1.46:8000"
echo ""

php artisan serve --host=192.168.1.46 --port=8000
