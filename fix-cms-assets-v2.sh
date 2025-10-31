#!/bin/bash

echo "========================================="
echo "SilverStripe 6 CMS Asset Fix (v2)"
echo "========================================="
echo ""

echo "Step 1: Remove existing resources to start fresh..."
ddev exec rm -rf public/resources
ddev exec rm -rf public/_resources
echo "   ✓ Cleaned"

echo ""
echo "Step 2: Clear all caches..."
ddev exec rm -rf silverstripe-cache/
ddev exec rm -rf .graphql-generated/
echo "   ✓ Caches cleared"

echo ""
echo "Step 3: Run composer vendor-expose with copy method..."
ddev composer vendor-expose copy
echo "   ✓ Vendor expose completed"

echo ""
echo "Step 4: Check if resources were created..."
if [ -d "public/resources" ]; then
    echo "   ✓ public/resources created"
    ddev exec ls public/resources/ | head -10
else
    echo "   ✗ public/resources still missing!"
    echo ""
    echo "   Trying alternative method: manual symlink..."
    ddev exec mkdir -p public/resources
    ddev exec ln -sf ../../vendor/silverstripe/admin/client/dist public/resources/silverstripe-admin
    echo "   ✓ Manual symlink created"
fi

echo ""
echo "Step 5: Run dev/build..."
ddev sake dev/build flush=1

echo ""
echo "Step 6: Set proper permissions..."
ddev exec chmod -R 755 public/resources 2>/dev/null || true

echo ""
echo "Step 7: Verify admin assets..."
if [ -f "public/resources/silverstripe/admin/client/dist/styles/bundle.css" ] || \
   [ -f "public/resources/silverstripe-admin/styles/bundle.css" ]; then
    echo "   ✓ Admin CSS found!"
else
    echo "   ✗ Admin CSS still not found"
    echo ""
    echo "   Searching for CSS files..."
    ddev exec find public/resources -name "*.css" -type f 2>/dev/null | head -5 || echo "   No CSS files found"
fi

echo ""
echo "========================================="
echo "Fix complete! Now:"
echo "1. Hard refresh browser (Ctrl+Shift+R)"
echo "2. Check browser console (F12) for errors"
echo "3. Visit: http://chat-bot-elastic-sim.ddev.site/admin"
echo "========================================="
