# SilverStripe 6 Resources Directory Fix

## The Problem

The CMS had no styling because resources were being created in a nested structure:
```
public/_resources/public/_resources/...  ❌ WRONG
```

Instead of:
```
public/_resources/silverstripe/admin/... ✅ CORRECT
```

## Root Cause

The `composer.json` had an incorrect `"expose": ["public"]` configuration that was causing the vendor-plugin to create nested directories.

## The Fix

### 1. Update composer.json

Changed from:
```json
"extra": {
    "expose": [
        "public"
    ],
    ...
}
```

To:
```json
"extra": {
    "resources-dir": "public/_resources",
    ...
}
```

### 2. Run the Fix Script

```bash
./fix-nested-resources.sh
```

This script will:
- Remove all broken nested directories
- Clear caches
- Reinstall composer dependencies
- Re-run vendor-expose with correct configuration
- Verify the resources were created properly

### 3. Verify the Fix

After running the script, check:

1. **Directory structure:**
   ```bash
   ls -la public/_resources/
   ```

   You should see:
   ```
   silverstripe/
   ├── admin/
   ├── asset-admin/
   ├── cms/
   └── ...
   ```

2. **Access the CSS directly:**

   Visit in your browser:
   ```
   http://chat-bot-elastic-sim.ddev.site/_resources/silverstripe/admin/client/dist/styles/bundle.css
   ```

   You should see CSS code, not a 404.

3. **Check the CMS:**

   Visit:
   ```
   http://chat-bot-elastic-sim.ddev.site/admin
   ```

   Hard refresh (Ctrl+Shift+R / Cmd+Shift+R) and the CMS should now be styled!

## Understanding SilverStripe 6 Resources

In SilverStripe 6, vendor resources are exposed to `public/_resources/` by default (note the underscore).

The URL structure is:
```
http://your-site.com/_resources/vendor-name/package-name/path/to/file.css
```

For example:
```
http://your-site.com/_resources/silverstripe/admin/client/dist/styles/bundle.css
```

## If It's Still Not Working

### Check 1: Verify vendor-plugin is installed
```bash
ddev composer show silverstripe/vendor-plugin
```

Should show version ^3.0

### Check 2: Manually expose resources
```bash
ddev composer vendor-expose
```

### Check 3: Check browser console
Open Developer Tools (F12) in your browser and check:
- **Console tab**: Any JavaScript errors?
- **Network tab**: Are CSS/JS files returning 404?

### Check 4: Verify .htaccess has FollowSymLinks
```bash
grep -i "FollowSymLinks" public/.htaccess
```

Should show:
```
Options +FollowSymLinks
```

### Check 5: Try copy method instead of symlinks
Some environments don't support symlinks. Try:
```bash
ddev composer vendor-expose copy
```

## References

- [SilverStripe 6 Upgrading Guide](https://docs.silverstripe.org/en/6/changelogs/6.0.0/)
- [Vendor Plugin Documentation](https://github.com/silverstripe/vendor-plugin)
