#!/bin/bash

# Fix SilverStripe CMS missing styles and JS
# This script exposes vendor assets to the public directory

echo "========================================="
echo "Fixing SilverStripe CMS Assets"
echo "========================================="
echo ""

# Step 1: Run vendor-expose to create resource symlinks
echo "1. Exposing vendor assets to public/resources..."
ddev composer vendor-expose

echo ""
echo "2. Running dev/build with flush..."
ddev sake dev/build flush=1

echo ""
echo "3. Clearing cache..."
ddev exec rm -rf silverstripe-cache/

echo ""
echo "========================================="
echo "Done! Now try:"
echo "1. Hard refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)"
echo "2. Visit: http://chat-bot-elastic-sim.ddev.site/admin"
echo "========================================="
