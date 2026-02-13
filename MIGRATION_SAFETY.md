# Migration Safety Protection 🛡️

This document explains the multi-layered protection system against accidental database wipes.

## 🚨 Problem

Running `php artisan migrate:fresh` or `migrate:refresh` **DELETES ALL DATA** from your database. This happened accidentally and we lost all data (luckily we had backups).

## ✅ Protection Layers

### Layer 1: Service Provider (Application Level)
**File:** `app/Providers/MigrationSafetyProvider.php`

- Blocks dangerous commands in production environment
- Requires `ALLOW_MIGRATE_FRESH=true` in `.env` OR `--force` flag
- Logs all dangerous command attempts
- **Blocked commands:**
  - `migrate:fresh`
  - `migrate:refresh`
  - `db:wipe`

### Layer 2: Bash Aliases (Shell Level)
**File:** `~/.bash_aliases`

- Intercepts `php artisan migrate:fresh` and shows warning
- Requires typing "I UNDERSTAND THE RISK" to proceed
- **Creates automatic backup** before proceeding
- Provides safe alternative commands

### Layer 3: Safe Migration Command
**Command:** `php artisan migrate:fresh-safe`

- Always creates backup before migrating
- Multiple confirmation prompts in production
- Shows current database stats before proceeding
- Automatic rollback support

### Layer 4: Environment Flag
**File:** `.env`

```env
ALLOW_MIGRATE_FRESH=false
```

Set to `true` only when you explicitly want to allow dangerous commands.

## 📋 How to Use

### ✅ SAFE: Use these commands

```bash
# Safe migration with automatic backup
php artisan migrate:fresh-safe

# Safe migration with seeding
php artisan migrate:fresh-safe --seed

# Or use the alias
migrate-fresh
migrate-fresh-seed

# Manual backup
db-backup
```

### ❌ BLOCKED: These are now protected

```bash
# These will show warnings and require confirmation
php artisan migrate:fresh
php artisan migrate:refresh
php artisan db:wipe
```

## 🔓 Emergency Override (NOT RECOMMENDED)

If you REALLY need to run the dangerous command:

**Option 1:** Set environment variable
```bash
# In .env file
ALLOW_MIGRATE_FRESH=true

# Then run command
php artisan migrate:fresh --force
```

**Option 2:** Use force flag
```bash
php artisan migrate:fresh --force
```

**Option 3:** Bypass all checks (DANGEROUS!)
```bash
# Type "I UNDERSTAND THE RISK" when prompted
php artisan migrate:fresh
```

## 📦 Backup Locations

All automatic backups are saved to:
- `storage/backups/pre-migrate-*.sql.gz` (before migrations)
- `/root/backups/daily_*.sql.gz` (daily backups via cron)

## 🔍 Checking Logs

All dangerous command attempts are logged:

```bash
tail -f storage/logs/laravel.log | grep "Dangerous migration"
```

## 🧪 Testing the Protection

Try to run a dangerous command:

```bash
php artisan migrate:fresh
```

You should see a big warning message and multiple confirmation prompts.

## 📚 Related Files

- `app/Console/Commands/SafeMigrateFresh.php` - Safe migration command
- `app/Providers/MigrationSafetyProvider.php` - Service provider blocking dangerous commands
- `~/.bash_aliases` - Bash-level protection
- `~/.bashrc` - Loads the aliases
- `.env` - Contains `ALLOW_MIGRATE_FRESH` flag

## 🆘 Restore from Backup

If something goes wrong, restore from backup:

```bash
# List available backups
ls -lh /root/backups/

# Restore from backup
gunzip -c /root/backups/daily_reforger_community_YYYYMMDD.sql.gz | \
    psql -U reforger -h 127.0.0.1 reforger_community
```

---

**Created:** 2026-02-12
**Author:** Claude
**Reason:** Prevent accidental database wipes after incident
