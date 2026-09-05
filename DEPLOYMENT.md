# RecruitSmart Deployment Guide

This guide covers deployment on a server with PHP, Composer, Node.js, npm, and PostgreSQL available.

## 1. Push the code to GitHub

```powershell
git add .
git commit -m "Prepare deployment"
git push origin main
```

Never upload `.env`, database passwords, or API keys.

## 2. Create the Neon database

1. Open https://neon.tech.
2. Create or open the `recruitment` project.
3. Use the `main` branch and the `neondb` database.
4. Open the Neon connection details.
5. Copy the full PostgreSQL connection string.

The string must include SSL settings, similar to:

```text
postgresql://USER:PASSWORD@HOST/neondb?sslmode=require&channel_binding=require
```

Keep the password private.

## 3. Create the project database

The Laravel migration creates the complete database structure. For a new or reset testing database, use:

```powershell
php artisan migrate:fresh --seed --force
```

For a database that already contains data, use only:

```powershell
php artisan migrate --force
```

Do not use `migrate:fresh` on a database with important data. It deletes all tables first.

## 4. Configure production environment values

Set these values in the server environment or `.env` file:

```text
APP_NAME=RecruitSmart
APP_ENV=production
APP_DEBUG=false
APP_KEY=YOUR_LARAVEL_APP_KEY
APP_URL=https://YOUR-RENDER-URL.onrender.com

DB_CONNECTION=pgsql
DB_SSLMODE=require
DB_URL=YOUR_FULL_NEON_CONNECTION_STRING

SESSION_DRIVER=database
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

LOG_CHANNEL=stderr
LOG_LEVEL=error

MAIL_MAILER=log
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME=RecruitSmart

AI_FALLBACK_ON_ERROR=true
AI_MODEL=openai/gpt-4o-mini
OPENROUTER_API_KEY=
```

Use the same full Neon connection string for `DB_URL`. Do not put `channel_binding=require` in `DB_SSLMODE`.

Correct:

```text
DB_SSLMODE=require
```

The full connection string belongs in `DB_URL`.

## 5. Create the Laravel app key

Run this on the server or another computer with PHP and Composer:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate --show
```

Put the displayed value in the server environment as `APP_KEY`.

## 6. Deploy

Run the existing deployment script from the project root on the production server:

```bash
bash deploy.sh
```

The script enables maintenance mode, pulls the `main` branch, installs Composer and npm dependencies, builds frontend assets, runs migrations, rebuilds Laravel caches, restarts queue workers, and restores the application.

To deploy a new commit:

```powershell
git add .
git commit -m "Update application"
git push origin main
```

## 7. Test the live app

Open the configured application URL:

```text
https://YOUR-APPLICATION-URL
```

Test Laravel health:

```text
https://YOUR-RENDER-URL.onrender.com/up
```

The page should show `Application up`.

## Common database error

If Render shows `current transaction is aborted`, check Neon first. For a database with no important data, run this in Neon SQL Editor:

```sql
DROP SCHEMA public CASCADE;
CREATE SCHEMA public;
```

Then run `php artisan migrate:fresh --seed --force` and rerun `deploy.sh`.

Do not run this command on a database that contains important data.
