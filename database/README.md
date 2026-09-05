# Database migrations

The project uses one baseline migration:

```text
database/migrations/2026_09_05_000000_create_recruitment_schema.php
```

It creates the complete application schema in dependency order:

1. Laravel framework tables
2. Roles and permissions
3. Departments and job postings
4. Applicants and applications
5. Interviews, assessments, recommendations, and offers
6. Onboarding, employees, documents, notifications, and activity logs

The baseline includes the columns used by the models, foreign keys, unique rules, and indexes.

## Fresh testing database

For a new or disposable testing database:

```bash
php artisan migrate:fresh --seed --force
```

This deletes all tables first, then creates the schema and seed data.

## Existing database

For a database that contains data:

```bash
php artisan migrate --force
```

Never run `migrate:fresh` on a database that contains data you need to keep.

## Future changes

Do not edit the baseline after it has been applied to a shared environment. Add a new, forward-only migration for future schema changes.

The old migration files and the old MySQL-to-PostgreSQL dump were removed because they described different versions of the schema and could not be safely replayed together.
