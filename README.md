# RecruitSmart

## 1. Project Overview

RecruitSmart is a Laravel web application for recruitment and onboarding. It includes a public travel and careers site, applicant and employee portals, HR recruitment workflows, interviews, assessments, offers, onboarding, documents, reports, notifications, activity logs, and optional AI decision support.

The application uses Blade for server-rendered pages and Vue components for selected dashboard widgets. The checked-in HTTP routes are web routes; no separate `routes/api.php` file is present.

## 2. Architecture

```text
Users
	|
	v
Browser
	|
	+--> Blade views and Vue widgets served by Vite
	|
	v
Laravel web routes (routes/web.php)
	|
	+--> Session authentication and role middleware
	+--> Controllers
					|
					+--> Application services, mailables, PDF reports, AI client
					|
					+--> Eloquent models
									|
									v
							Configured database

Optional external services:
	OpenRouter AI API, configured mail transport, and configured object storage
```

Application middleware is configured in `bootstrap/app.php`. The web stack appends `SecurityHeaders` and registers the `role` alias for `CheckRole`.

## 3. Tech Stack

| Area | Technology |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Authentication | Laravel session authentication, Laravel Breeze, Sanctum package |
| Authorization | Spatie Laravel Permission and custom `CheckRole` middleware |
| Frontend | Blade, Vue 3, Vite |
| Styling and assets | Tailwind CSS, Vite Laravel plugin |
| Database | SQLite by default in `.env.example`; PostgreSQL is configured by the deployment guide |
| PDF generation | `barryvdh/laravel-dompdf` |
| AI integration | OpenRouter chat completions with a rule-based fallback |
| Testing | PHPUnit 11 through Laravel's test runner |

## 4. Prerequisites

- PHP 8.2 or later
- Composer
- Node.js and npm
- A database supported by the configured Laravel connection. The repository template defaults to SQLite; `DEPLOYMENT.md` documents PostgreSQL/Neon deployment.
- Bash, Git, PHP, Composer, Node.js, and npm for `deploy.sh`

## 5. Project Structure

```text
app/
|-- Console/Commands/       Console commands
|-- Http/Controllers/       Web controllers, including auth and portals
|-- Http/Middleware/        CheckRole and SecurityHeaders
|-- Mail/                   Recruitment and onboarding mailables
|-- Models/                 Eloquent models
|-- Providers/              Application service providers
`-- Services/               AI, resume, submission, and activity services
bootstrap/                  Laravel application bootstrap
config/                     Application, database, mail, AI, and service config
database/                  Migrations, seeders, and factories
public/                     Public entry point and compiled Vite assets
resources/                 CSS, Vue entry point, components, and Blade views
routes/                    Web and console route definitions
scripts/                   Project scripts
storage/                   Logs, framework files, and application storage
tests/                     Feature and unit tests
artisan                    Laravel command-line entry point
deploy.sh                  Production deployment script
vite.config.js             Vite and Laravel asset configuration
composer.json              PHP dependencies and Composer scripts
package.json               JavaScript dependencies and npm scripts
phpunit.xml                PHPUnit configuration
```

## 6. Local Development Setup

From the project root:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configure the database and mail settings in `.env`, then create the schema and development data:

```bash
php artisan migrate --seed
npm run build
```

Start the application and Vite in separate terminals:

```bash
php artisan serve
npm run dev
```

The Laravel development server normally listens on `http://localhost:8000`.

Composer also provides:

```bash
composer setup   # install, create .env if needed, generate key, migrate, install npm packages, build
composer dev     # Laravel server, queue listener, Pail logs, and Vite via concurrently
composer test    # clear config and run the Laravel test suite
```

`composer setup` runs `migrate --force` without seeding. Use `php artisan migrate --seed` when sample roles and data are required.

## 7. Environment Variables

The template is `.env.example`. Do not commit `.env`, credentials, database passwords, or API keys.

| Group | Variables used by the repository |
|---|---|
| Application | `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `APP_LOCALE`, `APP_FALLBACK_LOCALE` |
| Database | `DB_CONNECTION`, `DB_URL`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_SSLMODE` |
| Session, cache, queue | `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_ENCRYPT`, `CACHE_STORE`, `QUEUE_CONNECTION` |
| Files and mail | `FILESYSTEM_DISK`, `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` |
| AI | `AI_PROVIDER`, `OPENROUTER_API_KEY`, `OPENROUTER_BASE_URL`, `AI_MODEL`, `AI_TIMEOUT`, `AI_JSON_MODE`, `AI_FALLBACK_ON_ERROR` |
| Frontend | `VITE_APP_NAME` |
| Optional services | Redis, Memcached, AWS/S3, Postmark, Resend, SES, Slack, and Sanctum variables supported by the Laravel configuration |

Configuration note: `.env.example` currently names the AI URL variable `AI_BASE_URL`, while `config/ai.php` reads `OPENROUTER_BASE_URL`. Set `OPENROUTER_BASE_URL` when overriding the OpenRouter endpoint.

## 8. Database & Migrations

The migration `database/migrations/2026_09_05_000000_create_recruitment_schema.php` creates the framework, RBAC, recruitment, hiring, onboarding, and operations tables. The main application tables are:

- Recruitment: `departments`, `job_positions`, `job_postings`, `applicants`, `applications`, `applicant_education`, `applicant_experience`, `applicant_skills`, `certifications`
- Hiring: `interviews`, `interview_assessments`, `ai_recommendations`, `offer_letters`
- Onboarding: `onboarding_checklists`, `onboarding`, `employee_profiles`, `uploaded_documents`
- Operations: `notifications`, `activity_logs`, `ai_pipeline_insights`
- Framework and auth: `users`, `sessions`, `cache`, `jobs`, `failed_jobs`, `password_reset_tokens`, and `personal_access_tokens`
- RBAC: `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`

Seeders are orchestrated by `database/seeders/DatabaseSeeder.php` and include roles and permissions, users, departments, job postings, applicants, and onboarding checklists.

```bash
php artisan migrate                 # apply pending migrations
php artisan migrate --seed          # apply migrations and run DatabaseSeeder
php artisan db:seed                  # run the canonical DatabaseSeeder
php artisan migrate:fresh --seed    # destructive reset for a disposable database
```

Do not use `migrate:fresh` on a database containing data that must be retained.

## 9. Backend / API

All application endpoints are defined in `routes/web.php` and return web responses. The primary route groups are:

| Area | Routes | Access |
|---|---|---|
| Public site | `/`, `/about`, `/tours`, `/destinations`, `/careers`, `/careers/{posting}`, `/contact` | Public |
| Authentication | `/login`, `/register`, `/forgot-password`, `/logout` | Guest or authenticated as defined in routes |
| Shared notifications | `/notifications` | Authenticated users |
| Applicant portal | `/applicant/*` | `Applicant` role |
| Employee portal | `/employee/*` | `Employee` role |
| HR workflows | `/dashboard`, `/recruitment/*`, `/analytics`, `/reports*` | HR staff roles |
| Administration | `/admin/users`, `/admin/departments`, `/admin/job-positions`, `/admin/activity-logs` | `Super Admin` and `HR Administrator` |

Notable backend services include `ApplicationSubmissionService`, `ApplicationStageNotificationService`, `ResumeParserService`, `ResumeTextExtractorService`, `AiRecommendationService`, `AiInsightService`, `AiProviderClient`, and `ActivityLogService`.

The application uses Laravel mailables for submission, shortlist, rejection, interview, offer, and hired events. Uploaded files use the configured Laravel filesystem disk. Reports are generated through Dompdf.

There is no checked-in REST API route file. Sanctum is installed, but the defined application routes use session authentication.

## 10. Authentication & Security

- Login uses Laravel sessions and `Auth::attempt()`.
- Registration assigns the `Applicant` role to the new user.
- User passwords use the `hashed` cast in `app/Models/User.php`.
- Login, registration, and password reset submissions use the `throttle:login` limiter.
- `AppServiceProvider` limits login attempts to five per minute by email and IP. An `api` limiter allows 60 requests per minute, although no API route group is defined.
- Laravel web middleware provides CSRF protection for state-changing routes.
- `SecurityHeaders` adds CSP, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, and `X-XSS-Protection` headers.
- HTTPS is forced in production by `AppServiceProvider`.
- Forwarded proxy headers are trusted in `bootstrap/app.php`.

The CSP currently permits inline and eval JavaScript and external assets from jsDelivr, Google Fonts, and cdnjs. Review this policy before exposing the application publicly.

## 11. System Modules

- Public site and careers: public pages, job listing, job details, and contact submission.
- Applicant portal: profile, resume parsing and preview, jobs, applications, withdrawal, offers, documents, education, experience, skills, certifications, and notifications.
- Recruitment operations: job postings, applications, candidate status changes, shortlisting, rejection, interviews, assessments, and calendar.
- AI decision support: application and posting recommendations, scoring, ranking, explanations, and rule-based fallback.
- Offers: offer creation, sending, response handling, and status tracking.
- Onboarding: onboarding records, checklist progress, employee profile creation, and employee portal access.
- Documents: upload, preview, download, verification, and deletion.
- Analytics and reports: dashboard analytics and candidate, hiring, and recruitment summary reports.
- Administration: users, departments, job positions, and activity logs.
- Notifications and mail: in-app notification records and recruitment lifecycle mailables.

Resume parsing accepts DOC, DOCX, PDF, TXT, JPG/JPEG, and PNG inputs through the resume services.

## 12. Role-Based Access Control (RBAC)

Roles seeded by `RoleAndPermissionSeeder`:

| Role | Route access |
|---|---|
| `Super Admin` | HR workflows and administration |
| `HR Administrator` | HR workflows and administration |
| `Recruitment Officer` | HR recruitment workflows, excluding admin routes |
| `Department Head` | HR dashboard, candidate review, interviews, assessments, AI dashboard, and calendar |
| `Applicant` | Applicant portal |
| `Employee` | Employee portal |

The seeded permission catalog covers dashboards, departments, positions, postings, applicants, applications, interviews, AI, offers, onboarding, reports, users, documents, notifications, activity logs, and calendars. Route access is enforced through the custom role middleware; views also reference permissions such as `generate_ai_recommendations`.

The canonical `CompleteDemoSeeder` creates the six Hiraya system accounts: `admin@hiraya.com`, `hr@hiraya.com`, `recruitment@hiraya.com`, `tours.head@hiraya.com`, `visa.head@hiraya.com`, and `sales.head@hiraya.com`. It also creates employee accounts `carlos@gmail.com`, `samantha.tan@gmail.com`, and `ramon.bautista@gmail.com`, linked to `employee_profiles` with positions Senior Travel Consultant, International Tour Coordinator, and Flight Ticketing & GDS Specialist. Applicant records have corresponding Applicant login accounts because the current application supports applicant authentication.

All seeded login accounts use the documented development password `password123`; change or remove these accounts outside local testing.

Seeded applicant applications:

| Applicant | Applied position | Stage |
|---|---|---|
| `juan.delacruz@example.com` | Senior Travel Consultant | `submitted` |
| `maria.santos@example.com` | International Tour Coordinator | `shortlisted` |
| `carlos.reyes@example.com` | Flight Ticketing & GDS Specialist | `for_interview` |
| `ana.garcia@example.com` | Visa & Passport Processing Officer | `assessed` |
| `pedro.aquino@example.com` | Destination Marketing Specialist | `recommended` |
| `liza.soberano@example.com` | Lead Tour Guide & Guest Experience Lead | `screening` |
| `marco.polo@example.com` | Senior Travel Consultant | `shortlisted` |
| `beatriz.alonzo@example.com` | International Tour Coordinator | `for_interview` |
| `gabriel.concepcion@example.com` | Flight Ticketing & GDS Specialist | `recommended` |
| `patricia.evangelista@example.com` | Destination Marketing Specialist | `screening` |
| `daniel.padilla@example.com` | Visa & Passport Processing Officer | `submitted` |

## 13. Deployment

`DEPLOYMENT.md` documents deployment with PostgreSQL/Neon. `deploy.sh` is a Bash script that:

1. Enables Laravel maintenance mode.
2. Pulls `main` from Git.
3. Installs production Composer dependencies.
4. Runs `npm ci` and `npm run build`.
5. Runs `php artisan migrate --force`.
6. Clears and rebuilds Laravel configuration, route, and view caches.
7. Restarts queue workers.
8. Updates `storage` and `bootstrap/cache` permissions.
9. Disables maintenance mode.

Run it from the project root on a Unix-like production server:

```bash
bash deploy.sh
```

The deployment script expects the server to provide Bash, Git, PHP, Composer, Node.js, npm, and the configured database. Review the environment values in `DEPLOYMENT.md` before deployment. The script does not create a storage symlink; configure file serving according to the selected filesystem disk.

## 14. Testing

PHPUnit is configured by `phpunit.xml`. Tests use SQLite in memory, array sessions/cache, synchronous queues, and array mail during the test run.

```bash
php artisan test
php artisan test --filter=SecurityHardeningTest
composer test
```

| Suite | Files |
|---|---|
| Feature | `AuthenticationTest`, `RbacSecurityTest`, `SecurityHardeningTest`, `RecruitmentWorkflowTest`, `OfferAndOnboardingTest`, `AiDecisionSupportTest`, `PublicSiteTest`, `CompleteDemoSeederTest` |
| Unit | `ApplicantModelTest` |

Frontend production assets can be validated with:

```bash
npm run build
```

## 15. Troubleshooting

| Symptom | Check |
|---|---|
| Database connection failure | Confirm `DB_CONNECTION` and the related connection variables. The template defaults to SQLite; deployment documentation uses PostgreSQL/Neon. |
| AI endpoint override has no effect | Use `OPENROUTER_BASE_URL`; `config/ai.php` does not read the `AI_BASE_URL` key in `.env.example`. |
| Missing development data | Run `php artisan db:seed` or `php artisan migrate --seed`. |
| Stale configuration | Run `php artisan config:clear`; deployment uses `php artisan optimize:clear` before rebuilding caches. |
| Uploads are not publicly reachable | Confirm `FILESYSTEM_DISK` and the server's file-serving configuration. `deploy.sh` does not run `storage:link`. |
| Deployment script will not run on Windows | Run it on a Unix-like server with Bash, or execute the equivalent deployment commands manually. |
| Destructive migration concern | Use `php artisan migrate --force` for existing data. Reserve `migrate:fresh --seed --force` for disposable databases. |

## 16. Contributing

1. Create a branch for the change.
2. Keep changes scoped to the relevant controller, service, model, view, route, migration, or test.
3. Run the applicable PHPUnit tests and `npm run build`.
4. Review configuration and migration changes for production impact.
5. Do not commit `.env`, credentials, API keys, or generated secrets.

## 17. License

The project declares the MIT license in `composer.json`. No standalone license file is present in the repository.

## 18. Quick Reference

```bash
# Install dependencies
composer install
npm install

# Configure and initialize
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Develop
php artisan serve
npm run dev

# Validate
composer test
npm run build

# Deploy on a Unix-like server
bash deploy.sh
```

Key files:

- `routes/web.php`: HTTP route definitions
- `bootstrap/app.php`: application middleware and route bootstrap
- `database/migrations/2026_09_05_000000_create_recruitment_schema.php`: primary schema migration
- `database/seeders/DatabaseSeeder.php`: development seed orchestration
- `database/seeders/CompleteDemoSeeder.php`: idempotent Hiraya demo dataset
- `config/ai.php`: AI provider configuration
- `DEPLOYMENT.md`: PostgreSQL/Neon deployment guide
- `deploy.sh`: automated production deployment procedure
