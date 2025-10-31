#!/bin/bash

echo "========================================="
echo "Fixing Nested Resources Directory Issue"
echo "========================================="
echo ""

echo "Step 1: Cleaning up broken nested directories..."
ddev exec rm -rf public/_resources
ddev exec rm -rf public/resources
ddev exec rm -rf _resources
echo "   ✓ Cleaned"

echo ""
echo "Step 2: Clearing all caches..."
ddev exec rm -rf silverstripe-cache/
ddev exec rm -rf .graphql-generated/
echo "   ✓ Caches cleared"

echo ""
echo "Step 3: Updating composer configuration..."
# composer.json has been updated to use:
#   "public-dir": "public"
#   "resources-dir": "_resources"
# This will create public/_resources
echo "   ✓ Configuration updated (check composer.json)"

echo ""
echo "Step 4: Running composer install to regenerate vendor-expose..."
ddev composer install

echo ""
echo "Step 5: Explicitly running vendor-expose..."
ddev composer vendor-expose

echo ""
echo "Step 6: Checking what was created..."
echo ""
echo "Contents of public/:"
ddev exec ls -la public/ | grep -E "^d.*_resources|^d.*resources"

if [ -d "public/_resources" ]; then
    echo ""
    echo "Contents of public/_resources/:"
    ddev exec ls public/_resources/ | head -10
fi

echo ""
echo "Step 7: Searching for admin assets..."
ddev exec find public -name "bundle.css" -type f 2>/dev/null | head -5

echo ""
echo "Step 8: Running dev/build..."
ddev sake dev/build flush=1

echo ""
echo "========================================="
echo "Fix Complete!"
echo ""
echo "The resources should now be at:"
echo "  public/_resources/"
echo ""
echo "Try accessing:"
echo "  http://chat-bot-elastic-sim.ddev.site/_resources/silverstripe/admin/client/dist/styles/bundle.css"
echo ""
echo "Then visit: http://chat-bot-elastic-sim.ddev.site/admin"
echo "========================================="
