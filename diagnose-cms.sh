#!/bin/bash

echo "========================================="
echo "SilverStripe CMS Diagnostics"
echo "========================================="
echo ""

echo "1. Checking if public/resources exists..."
if [ -d "public/resources" ]; then
    echo "   ✓ public/resources EXISTS"
    echo ""
    echo "   Contents:"
    ddev exec ls -la public/resources/ | head -20
else
    echo "   ✗ public/resources DOES NOT EXIST"
fi

echo ""
echo "2. Checking for admin assets..."
if [ -d "public/resources/silverstripe/admin" ]; then
    echo "   ✓ admin resources found"
    ddev exec ls -la public/resources/silverstripe/admin/ | head -10
else
    echo "   ✗ admin resources NOT found"
fi

echo ""
echo "3. Checking vendor/silverstripe/admin..."
if [ -d "vendor/silverstripe/admin" ]; then
    echo "   ✓ vendor package exists"
    ddev exec ls vendor/silverstripe/admin/client/ 2>/dev/null || echo "   No client directory found"
else
    echo "   ✗ vendor package NOT found"
fi

echo ""
echo "4. Testing resource URL accessibility..."
echo "   Trying: http://chat-bot-elastic-sim.ddev.site/resources/silverstripe/admin/client/dist/styles/bundle.css"
RESPONSE=$(ddev exec curl -s -o /dev/null -w "%{http_code}" http://localhost/resources/silverstripe/admin/client/dist/styles/bundle.css 2>/dev/null)
if [ "$RESPONSE" = "200" ]; then
    echo "   ✓ Resources are accessible (HTTP $RESPONSE)"
else
    echo "   ✗ Resources NOT accessible (HTTP $RESPONSE)"
fi

echo ""
echo "5. Checking .htaccess in public/..."
if grep -q "FollowSymLinks" public/.htaccess 2>/dev/null; then
    echo "   ✓ FollowSymLinks configured"
else
    echo "   ✗ FollowSymLinks NOT configured"
fi

echo ""
echo "6. Checking vendor-plugin version..."
ddev composer show silverstripe/vendor-plugin | grep versions

echo ""
echo "========================================="
echo "Please share this output for diagnosis!"
echo "========================================="
