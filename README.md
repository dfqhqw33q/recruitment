# RecruitSmart

> AI-Assisted Recruitment & Onboarding Platform built with **Laravel 12** + **Vue 3** + **Vite**.

---

## 🚀 Quick Start (Local Development)

### Prerequisites
- PHP 8.2+
- Node.js 20+
- Composer 2+
- PostgreSQL 14+ (or use Docker)

### Setup
```bash
# 1. Clone the repository
git clone https://github.com/your-org/recruitsmart.git
cd recruitsmart

# 2. Install dependencies
composer install
npm install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Configure PostgreSQL in .env, then:
php artisan migrate --seed

# 5. Build frontend assets
npm run build

# 6. Start the development server
php artisan serve
npm run dev
```

### Using Docker (Local)
```bash
docker compose -f docker-compose.dev.yml up -d
docker compose -f docker-compose.dev.yml exec app php artisan migrate --seed
```

Open `http://localhost:8000`

---

## 🐳 Docker (Production)

```bash
# Configure production environment variables in your hosting provider
# Set APP_KEY, PostgreSQL DB_URL, OPENROUTER_API_KEY, and APP_URL

php artisan key:generate --force

# Start all services (app, queue worker, scheduler, MySQL)
docker compose up -d

# View logs
docker compose logs -f app
```

---

## 🔄 CI/CD Pipeline (GitHub Actions)

| Workflow | Trigger | Actions |
|---|---|---|
| **CI** (`.github/workflows/ci.yml`) | Push to `main` / `develop`, PRs | PHPUnit tests, Laravel Pint lint, Vite build |
| **CD** (`.github/workflows/deploy.yml`) | Push to `main` | Docker build & push, SSH zero-downtime deploy |

### Required GitHub Secrets
```
DOCKER_USERNAME       — Docker Hub username
DOCKER_PASSWORD       — Docker Hub token
DEPLOY_HOST           — Production server IP/hostname
DEPLOY_USER           — SSH user on server
DEPLOY_SSH_KEY        — Private SSH key for server access
```

---

## 🛡️ Security

- HTTP Security Headers middleware (`X-Frame-Options`, `X-Content-Type-Options`, `CSP`)
- Login/register rate-limiting (5 requests/min per IP via `throttle:login`)
- HTTPS enforced in production via `URL::forceScheme('https')`
- CSRF protection on all state-changing routes

---

## 🧪 Testing

```bash
# Run full test suite (uses SQLite in-memory)
php artisan test

# Run a specific test class
php artisan test --filter=SecurityHardeningTest
```

| Suite | Tests |
|---|---|
| Unit | `ApplicantModelTest` |
| Feature | `AuthenticationTest`, `RbacSecurityTest`, `SecurityHardeningTest`, `RecruitmentWorkflowTest`, `OfferAndOnboardingTest`, `AiDecisionSupportTest`, `PublicSiteTest` |

---

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/        — Feature controllers (Auth, Recruitment, AI, Portals)
│   └── Middleware/         — SecurityHeaders, CheckRole
├── Models/                 — 21 Eloquent models
├── Providers/              — AppServiceProvider (rate limiters)
└── Services/               — AiProviderClient, AiInsightService
resources/
├── css/app.css             — Design system (glassmorphism, Google Fonts, animations)
├── js/
│   ├── app.js              — Vue 3 root
│   └── components/         — PipelineKanbanWidget, ApplicantProgressTracker
└── views/                  — Blade templates (HR, Applicant, Employee, Public portals)
.github/workflows/          — CI/CD pipelines
docker-compose.yml          — Production Docker setup
docker-compose.dev.yml      — Local development Docker setup
deploy.sh                   — Manual zero-downtime deployment script
```
