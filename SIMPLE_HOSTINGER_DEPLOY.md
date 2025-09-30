# Simple Hostinger Deployment

## Quick Deploy (Recommended for Shared Hosting)

### 1. Prepare Files
```bash
# Remove development files
rm -rf vendor/ node_modules/ .git/
zip -r ayapoll.zip . -x "*.env*"
```

### 2. Upload & Extract
- Upload `ayapoll.zip` to Hostinger File Manager
- Extract to `public_html/`

### 3. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 4. Configure Environment
```env
# .env
APP_URL=https://yourdomain.com
DB_HOST=localhost
DB_DATABASE=u297970444_eya
DB_USERNAME=u297970444_eya
DB_PASSWORD=your_password
```

### 5. Setup Database
```bash
php artisan migrate --force
php artisan key:generate --force
```

### 6. Set Permissions
```bash
chmod -R 755 storage/ bootstrap/cache/
```

### 7. Add Cron Job
```
*/5 * * * * cd /home/u297970444/public_html && php artisan queue:work --stop-when-empty
```

**Done.** Your app is live.

## Alternative: Use Hostinger's Built-in Tools
- Use Hostinger's "Auto Installer" for Laravel if available
- Use their database import tool
- Use their file manager for uploads

The complex symlink strategy is overkill for shared hosting.