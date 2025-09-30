# Echara Voting System - Hostinger Deployment Guide

## 🚀 Quick Deployment Overview

This guide provides step-by-step instructions for deploying the Echara Voting System on Hostinger shared hosting with atomic release management and symlink strategy.

## 📋 Prerequisites

- Hostinger shared hosting account
- Domain name configured
- SSH access (if available)
- FTP/File Manager access
- MySQL database access

## 🏗️ Phase 1: Initial Setup

### 1.1 Database Setup

1. **Create Database in Hostinger Panel:**
   - Go to Databases → MySQL Databases
   - Create database: `u297970444_eya` (or your preferred name)
   - Create database user with full privileges
   - Note down database credentials

2. **Import Database Schema:**
   - Use phpMyAdmin or database import tool
   - Import `database/schema.sql`
   - Verify all tables are created successfully

### 1.2 Directory Structure Setup

Run the initial setup script via SSH or create manually:

```bash
# If SSH is available
chmod +x hostinger-setup.sh
./hostinger-setup.sh
```

**Manual Setup (via File Manager):**
```
/home/u297970444/domains/yourdomain.com/
├── releases/          # All deployment versions
├── shared/           # Shared files across deployments
│   └── storage/      # Laravel storage directory
├── backups/          # Deployment backups
└── public_html/      # Symlink to current release
```

## 🔧 Phase 2: Environment Configuration

### 2.1 Environment File

1. Copy `.env.hostinger` to your deployment
2. Update the following variables:

```env
APP_URL=https://yourdomain.com
DB_DATABASE=u297970444_eya
DB_USERNAME=u297970444_eya
DB_PASSWORD=your_actual_password

MAIL_HOST=smtp.hostinger.com
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
```

### 2.2 Generate Application Key

```bash
php artisan key:generate --force
```

## 📦 Phase 3: Deployment Process

### 3.1 Prepare Release Package

1. **Create deployment package:**
   ```bash
   # Exclude vendor directory and sensitive files
   zip -r ayapoll-release.zip . -x "vendor/*" "node_modules/*" ".git/*" ".env"
   ```

2. **Upload to Hostinger:**
   - Upload `ayapoll-release.zip` to `/tmp/` directory
   - Upload deployment scripts to your home directory

### 3.2 Run Deployment

```bash
# Make deployment script executable
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

The deployment script will:
- Extract files to new release directory
- Install Composer dependencies
- Set up environment
- Create symlinks
- Run migrations
- Cache configurations
- Switch to new release atomically

## ⚙️ Phase 4: Web Server Configuration

### 4.1 .htaccess Setup

1. Copy `.htaccess.hostinger` to your public_html root as `.htaccess`
2. Ensure mod_rewrite is enabled
3. Verify security headers are applied

### 4.2 PHP Configuration

Add to `.htaccess` or create `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
max_input_vars = 3000
memory_limit = 256M
```

## 🕐 Phase 5: Cron Jobs Setup

### 5.1 Add Cron Jobs in Hostinger Panel

Go to Advanced → Cron Jobs and add:

```bash
# Laravel Scheduler (every minute)
* * * * * cd /home/u297970444/domains/yourdomain.com/public_html && php artisan schedule:run

# Queue Worker (every 5 minutes)
*/5 * * * * cd /home/u297970444/domains/yourdomain.com/public_html && timeout 300 php artisan queue:work --timeout=60 --tries=3 --max-jobs=50

# System cleanup (daily at 2 AM)
0 2 * * * cd /home/u297970444/domains/yourdomain.com/public_html && php artisan cleanup:expired-files

# Cache optimization (daily at 4 AM)
0 4 * * * cd /home/u297970444/domains/yourdomain.com/public_html && php artisan optimize
```

## 🔒 Phase 6: Security Configuration

### 6.1 File Permissions

```bash
# Set proper permissions
chmod -R 755 /home/u297970444/domains/yourdomain.com/
chmod -R 775 /home/u297970444/domains/yourdomain.com/shared/storage/
```

### 6.2 SSL Certificate

1. Enable SSL in Hostinger panel
2. Force HTTPS redirects
3. Update APP_URL to use https://

## 📊 Phase 7: Monitoring & Maintenance

### 7.1 Log Monitoring

- Check Laravel logs: `storage/logs/laravel.log`
- Monitor error logs in Hostinger panel
- Set up log rotation via cron

### 7.2 Performance Optimization

```bash
# Run after each deployment
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 🔄 Phase 8: Update Process

### 8.1 Atomic Updates

1. Create new release package
2. Upload to `/tmp/ayapoll-release.zip`
3. Run deployment script
4. Automatic rollback on failure

### 8.2 Rollback Process

```bash
# List available releases
ls -la /home/u297970444/domains/yourdomain.com/releases/

# Rollback to previous release
ln -nfs /home/u297970444/domains/yourdomain.com/releases/TIMESTAMP /home/u297970444/domains/yourdomain.com/public_html
```

## 🧪 Phase 9: Testing

### 9.1 Deployment Verification

1. **Database Connection:**
   ```bash
   php artisan migrate:status
   ```

2. **Application Health:**
   ```bash
   php artisan health:check
   ```

3. **Queue System:**
   ```bash
   php artisan queue:work --once
   ```

### 9.2 Functional Testing

- [ ] User registration works
- [ ] Email notifications sent
- [ ] File uploads functional
- [ ] Voting system operational
- [ ] Admin panel accessible

## 📁 Phase 10: File Structure

```
public_html/ (symlink to current release)
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/ (symlink to shared/storage)
├── vendor/
├── .env
├── .htaccess
├── artisan
└── composer.json

shared/
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/

releases/
├── 20250101_120000/
├── 20250101_130000/
└── 20250101_140000/
```

## 🚨 Troubleshooting

### Common Issues

1. **Permission Errors:**
   ```bash
   chmod -R 775 storage/
   chmod -R 775 bootstrap/cache/
   ```

2. **Database Connection:**
   - Verify database credentials
   - Check database server status
   - Ensure database exists

3. **Composer Issues:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Queue Not Processing:**
   - Check cron jobs are running
   - Verify queue configuration
   - Monitor failed jobs table

## 📞 Support

For deployment issues:
1. Check Laravel logs
2. Verify Hostinger error logs
3. Test database connectivity
4. Validate file permissions

## 🔐 Security Checklist

- [ ] SSL certificate installed
- [ ] .env file secured
- [ ] Database credentials updated
- [ ] File permissions set correctly
- [ ] Security headers configured
- [ ] Sensitive directories protected
- [ ] Admin accounts secured
- [ ] Backup strategy implemented

## 📈 Performance Optimization

1. **Enable OPcache** (if available)
2. **Use database caching**
3. **Optimize images**
4. **Enable compression**
5. **Monitor resource usage**

---

**Deployment completed successfully!** 🎉

Your Echara Voting System is now live and ready for production use.