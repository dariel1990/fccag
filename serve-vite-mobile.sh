#!/bin/bash

echo "Starting Vite dev server for mobile testing..."
echo "This will allow the mobile emulator to access the dev server."
echo ""
echo "Vite will be available at: http://192.168.1.46:5173"
echo "Your Laravel app should be at: http://192.168.1.46:8000"
echo ""
echo "Make sure to also run: php artisan serve --host=0.0.0.0 --port=8000"
echo ""

VITE_HMR_HOST=192.168.1.46 npm run dev
