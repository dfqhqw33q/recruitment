# RecruitSmart Deployment Guide

This guide deploys the Laravel and Vue application for free.

Services used:

- GitHub stores the code.
- Neon stores the PostgreSQL database.
- Render runs the application.

## 1. Push the code to GitHub

The repository must contain the project files and the `Dockerfile`.

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

## 4. Create the Render service

1. Open https://render.com.
2. Choose `New` and then `Web Service`.
3. Select the GitHub repository.
4. Use these settings:

```text
Name: recruitment
Runtime: Docker
Branch: main
Region: Ohio
Root Directory: blank
Plan: Free
```

Leave the build and start commands blank. Render uses the repository `Dockerfile`.

## 5. Add Render environment values

In Render, open `Environment` and add these values:

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
CACHE_STORE=database
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

## 6. Create the Laravel app key

Run this on a computer with PHP and Composer:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate --show
```

Put the displayed value in Render as `APP_KEY`.

Do not create two `APP_KEY` rows in Render.

## 7. Deploy

Click `Create Web Service` in Render.

Render will build the Docker image, build the Vue files, connect to Neon, and start Laravel.

To redeploy later from the terminal:

```powershell
$render = "PATH_TO_RENDER.exe"
& $render deploys create SERVICE_ID --wait --confirm
```

Or push a new commit:

```powershell
git add .
git commit -m "Update application"
git push origin main
```

Render will deploy the new commit automatically.

## 8. Test the live app

Open the Render URL:

```text
https://YOUR-RENDER-URL.onrender.com
```

Test Laravel health:

```text
https://YOUR-RENDER-URL.onrender.com/up
```

The page should show `Application up`.

## 9. Important free-plan limits

- Render may sleep when nobody is using the app.
- Local uploaded files may disappear after a restart.
- Log mail does not send real emails.
- Background jobs do not run continuously with the free setup.
- Store important files in cloud storage before using this for real business data.

## Common database error

If Render shows `current transaction is aborted`, check Neon first. For a database with no important data, run this in Neon SQL Editor:

```sql
DROP SCHEMA public CASCADE;
CREATE SCHEMA public;
```

Then run `php artisan migrate:fresh --seed --force` and redeploy.

Do not run this command on a database that contains important data.
