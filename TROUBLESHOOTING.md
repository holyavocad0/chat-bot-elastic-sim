# Troubleshooting Guide

## CMS Has No Styles or JavaScript

### Symptoms
- The CMS admin interface appears unstyled (plain HTML)
- No JavaScript functionality
- Missing buttons, forms look broken
- Navigation doesn't work

### Cause
The vendor assets (CSS/JS) haven't been exposed to the `public/resources/` directory. SilverStripe uses the `vendor-expose` system to create symlinks from vendor packages to the public webroot.

### Solution

Run the following commands:

```bash
# Expose vendor assets to public/resources/
ddev composer vendor-expose

# Rebuild database and flush cache
ddev sake dev/build flush=1

# Clear cache directory
ddev exec rm -rf silverstripe-cache/
```

Or use the convenience script:

```bash
chmod +x fix-cms-assets.sh
./fix-cms-assets.sh
```

### Verification

After running the fix:

1. Check that `public/resources/` directory exists:
   ```bash
   ddev exec ls -la public/resources/
   ```

   You should see directories like:
   - `silverstripe/admin/`
   - `silverstripe/asset-admin/`
   - `silverstripe/cms/`
   - etc.

2. Visit the CMS: `http://chat-bot-elastic-sim.ddev.site/admin`

3. Hard refresh your browser (Ctrl+Shift+R on Windows/Linux, Cmd+Shift+R on Mac)

### Why This Happens

In SilverStripe 4+, vendor assets are not automatically copied to the webroot. Instead:

1. The `silverstripe/vendor-plugin` package creates symlinks
2. These symlinks point from `public/resources/` to files in `vendor/`
3. The `composer vendor-expose` command creates these symlinks
4. Sometimes this needs to be run manually, especially after:
   - Fresh composer install
   - Updating packages
   - Changing server environments

### Still Not Working?

If the CMS is still unstyled after running the fix:

1. **Check file permissions:**
   ```bash
   ddev exec ls -la public/resources/
   ```
   Make sure the symlinks are readable.

2. **Check your web server configuration:**
   Apache needs `FollowSymLinks` enabled in `.htaccess` (already configured).

3. **Check browser console for errors:**
   Open Developer Tools (F12) and check the Console and Network tabs for 404 errors.

4. **Try a different browser:**
   Sometimes browser caching can cause issues.

5. **Check if resources are accessible:**
   Try visiting directly:
   ```
   http://chat-bot-elastic-sim.ddev.site/resources/silverstripe/admin/client/dist/styles/bundle.css
   ```
   You should see CSS code, not a 404 error.

### For Production Deployment

In production, you may want to copy files instead of using symlinks:

```bash
# Copy instead of symlink (for hosts that don't support symlinks)
composer vendor-expose copy
```

Or configure this in `composer.json`:

```json
{
  "extra": {
    "vendor-expose": {
      "copy-method": true
    }
  }
}
```

---

## Other Common Issues

### Elasticsearch Connection Failed

**Symptoms:** Search doesn't work, errors about Elasticsearch connection.

**Solution:**
```bash
# Check if Elasticsearch is running
ddev describe

# Restart DDEV services
ddev restart

# Test Elasticsearch connection
curl http://localhost:9200
```

### LLM API Errors

**Symptoms:** Chat bot returns error messages instead of answers.

**Solution:**
- Check API key in `.env` is correct
- Verify you have credits/quota with your LLM provider
- Check the logs: `ddev logs`

### Pages Not Appearing in Search

**Symptoms:** Search returns no results even though pages exist.

**Solution:**
```bash
# Reindex all pages
ddev sake dev/tasks/PopulateDummyPagesTask

# Or manually trigger indexing by re-publishing pages in the CMS
```

### CORS Errors from Nuxt Frontend

**Symptoms:** Browser console shows CORS errors when calling the API.

**Solution:**
Update `CORS_ALLOWED_ORIGINS` in `.env`:
```env
CORS_ALLOWED_ORIGINS="http://localhost:3000,http://127.0.0.1:3000"
```
