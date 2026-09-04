<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PDO;

class MigrateToPostgres extends Command
{
    protected $signature = 'db:migrate-to-postgres 
                            {--host= : Target PostgreSQL host}
                            {--port= : Target PostgreSQL port (default: 5432)}
                            {--database= : Target PostgreSQL database name (default: recruitment_tt)}
                            {--username= : Target PostgreSQL username}
                            {--password= : Target PostgreSQL password}
                            {--sslmode= : Target PostgreSQL SSL mode (prefer, require, disable)}
                            {--export : Generate standalone PostgreSQL SQL dump file without connecting to live PG}
                            {--wipe : Wipe target PostgreSQL tables before importing}';

    protected $description = 'Migrate schema and data from MySQL (recruitment_tt) to PostgreSQL';

    /**
     * Tables in strict foreign key order
     */
    protected array $tablesInOrder = [
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
        'departments',
        'job_positions',
        'permissions',
        'roles',
        'role_has_permissions',
        'users',
        'model_has_roles',
        'model_has_permissions',
        'password_reset_tokens',
        'personal_access_tokens',
        'job_postings',
        'applicants',
        'applicant_education',
        'applicant_experience',
        'applicant_skills',
        'certifications',
        'applications',
        'interviews',
        'interview_assessments',
        'ai_recommendations',
        'offer_letters',
        'onboarding_checklists',
        'employee_profiles',
        'onboarding',
        'uploaded_documents',
        'notifications',
        'activity_logs',
        'ai_pipeline_insights',
    ];

    /**
     * Boolean columns requiring strict true/false conversion
     */
    protected array $booleanColumns = [
        'applications' => ['is_knocked_out'],
        'notifications' => ['is_read'],
        'onboarding_checklists' => ['is_required'],
    ];

    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('  RecruitSmart Database Migration to PostgreSQL     ');
        $this->info('====================================================');

        if ($this->option('export')) {
            return $this->exportPostgresDump();
        }

        // Configure target PostgreSQL connection dynamically
        $host = $this->option('host') ?: env('DB_HOST', '127.0.0.1');
        $port = $this->option('port') ?: env('DB_PORT', '5432');
        $database = $this->option('database') ?: env('DB_DATABASE', 'recruitment_tt');
        $username = $this->option('username') ?: env('DB_USERNAME', 'postgres');
        $password = $this->option('password') ?? env('DB_PASSWORD', '');
        $sslmode = $this->option('sslmode') ?: env('DB_SSLMODE', 'prefer');

        Config::set('database.connections.pgsql_target', [
            'driver' => 'pgsql',
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => $sslmode,
        ]);

        $this->info("Connecting to MySQL source ('mysql')...");
        try {
            DB::connection('mysql')->getPdo();
            $this->info('Source MySQL connected successfully.');
        } catch (\Throwable $e) {
            $this->error('Failed to connect to MySQL: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("Connecting to PostgreSQL target ($host:$port/$database)...");
        try {
            DB::connection('pgsql_target')->getPdo();
            $this->info('Target PostgreSQL connected successfully.');
        } catch (\Throwable $e) {
            $this->error('Failed to connect to PostgreSQL: ' . $e->getMessage());
            $this->line('Tip: If your cloud database requires SSL, add --sslmode=require');
            $this->line('Tip: You can also use --export to generate a standalone .sql file.');
            return Command::FAILURE;
        }

        // Run migrations on PostgreSQL
        $this->info('Running migrations on target PostgreSQL...');
        Config::set('database.default', 'pgsql_target');
        Artisan::call('migrate', [
            '--database' => 'pgsql_target',
            '--force' => true,
        ]);
        $this->line(Artisan::output());

        // Transfer data
        $targetPdo = DB::connection('pgsql_target')->getPdo();
        $sourcePdo = DB::connection('mysql')->getPdo();

        $this->info('Beginning data migration...');

        // Disable foreign key constraints temporarily
        $targetPdo->exec("SET session_replication_role = 'replica';");

        $stats = [];

        foreach ($this->tablesInOrder as $table) {
            $srcCountCheck = $sourcePdo->query("SHOW TABLES LIKE '$table'")->rowCount();
            if ($srcCountCheck === 0) {
                continue;
            }

            $targetPdo->exec("TRUNCATE TABLE \"$table\" CASCADE;");

            $sourceRows = $sourcePdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            $count = count($sourceRows);

            if ($count > 0) {
                $columns = array_keys($sourceRows[0]);
                $quotedColumns = array_map(fn($c) => "\"$c\"", $columns);
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $sql = "INSERT INTO \"$table\" (" . implode(', ', $quotedColumns) . ") VALUES ($placeholders)";
                $stmt = $targetPdo->prepare($sql);

                $boolCols = $this->booleanColumns[$table] ?? [];

                foreach ($sourceRows as $row) {
                    $values = [];
                    foreach ($columns as $col) {
                        $val = $row[$col];
                        if (in_array($col, $boolCols)) {
                            $val = $val ? 'true' : 'false';
                        }
                        $values[] = $val;
                    }
                    $stmt->execute($values);
                }
            }

            // Reset serial sequence if id exists
            try {
                $seqCheck = $targetPdo->query("SELECT pg_get_serial_sequence('$table', 'id')")->fetchColumn();
                if ($seqCheck) {
                    $targetPdo->exec("SELECT setval('$seqCheck', COALESCE((SELECT MAX(id) FROM \"$table\"), 1));");
                }
            } catch (\Throwable) {
                // Ignore for tables without serial id
            }

            // Verify count
            $targetCount = (int) $targetPdo->query("SELECT COUNT(*) FROM \"$table\"")->fetchColumn();
            $stats[] = [
                'Table' => $table,
                'MySQL Count' => $count,
                'PostgreSQL Count' => $targetCount,
                'Status' => ($count === $targetCount) ? 'OK' : 'MISMATCH',
            ];
        }

        // Re-enable foreign key constraints
        $targetPdo->exec("SET session_replication_role = 'origin';");

        $this->table(['Table', 'MySQL Count', 'PostgreSQL Count', 'Status'], $stats);
        $this->info('Migration completed successfully!');

        // Also generate the SQL dump as backup
        $this->exportPostgresDump();

        return Command::SUCCESS;
    }

    /**
     * Generate standalone PostgreSQL SQL Dump file
     */
    public function exportPostgresDump(): int
    {
        $this->info('Exporting PostgreSQL-compatible SQL dump from MySQL...');

        try {
            $sourcePdo = DB::connection('mysql')->getPdo();
        } catch (\Throwable $e) {
            $this->error('Failed to connect to MySQL: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $outputPath = database_path('recruitment_tt_postgres.sql');
        $file = fopen($outputPath, 'w');

        fwrite($file, "-- ====================================================\n");
        fwrite($file, "-- PostgreSQL Dump for recruitment_tt\n");
        fwrite($file, "-- Generated on " . date('Y-m-d H:i:s') . "\n");
        fwrite($file, "-- Target: PostgreSQL 14+\n");
        fwrite($file, "-- ====================================================\n\n");

        fwrite($file, "SET statement_timeout = 0;\n");
        fwrite($file, "SET lock_timeout = 0;\n");
        fwrite($file, "SET client_encoding = 'UTF8';\n");
        fwrite($file, "SET standard_conforming_strings = on;\n");
        fwrite($file, "SET check_function_bodies = false;\n");
        fwrite($file, "SET client_min_messages = warning;\n");
        fwrite($file, "SET row_security = off;\n");
        fwrite($file, "SET session_replication_role = 'replica';\n\n");

        $totalRows = 0;

        foreach ($this->tablesInOrder as $table) {
            $srcTableCheck = $sourcePdo->query("SHOW TABLES LIKE '$table'")->rowCount();
            if ($srcTableCheck === 0) {
                continue;
            }

            $rows = $sourcePdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            $count = count($rows);
            $totalRows += $count;

            fwrite($file, "--\n-- Data for table: $table ($count rows)\n--\n");

            if ($count > 0) {
                $columns = array_keys($rows[0]);
                $quotedCols = implode(', ', array_map(fn($c) => "\"$c\"", $columns));
                $boolCols = $this->booleanColumns[$table] ?? [];

                foreach ($rows as $row) {
                    $formattedValues = [];
                    foreach ($columns as $col) {
                        $val = $row[$col];
                        if (is_null($val)) {
                            $formattedValues[] = 'NULL';
                        } elseif (in_array($col, $boolCols)) {
                            $formattedValues[] = $val ? 'TRUE' : 'FALSE';
                        } elseif (is_numeric($val) && !in_array($col, ['phone', 'tin_no', 'sss_no', 'philhealth_no', 'pagibig_no', 'reference_code'])) {
                            $formattedValues[] = $val;
                        } else {
                            $escaped = str_replace("'", "''", $val);
                            $formattedValues[] = "'$escaped'";
                        }
                    }

                    fwrite($file, "INSERT INTO \"$table\" ($quotedCols) VALUES (" . implode(', ', $formattedValues) . ") ON CONFLICT DO NOTHING;\n");
                }
                fwrite($file, "\n");
            }

            // Sequence update if table has id
            $hasId = $sourcePdo->query("SHOW COLUMNS FROM `$table` LIKE 'id'")->rowCount() > 0;
            if ($hasId && $table !== 'notifications') {
                fwrite($file, "SELECT setval(pg_get_serial_sequence('\"$table\"', 'id'), COALESCE((SELECT MAX(id) FROM \"$table\"), 1));\n\n");
            }
        }

        fwrite($file, "SET session_replication_role = 'origin';\n");
        fwrite($file, "-- End of Dump. Total records migrated: $totalRows\n");
        fclose($file);

        $size = round(filesize($outputPath) / 1024, 2);
        $this->info("PostgreSQL SQL dump generated successfully: $outputPath ($size KB, $totalRows records).");
        return Command::SUCCESS;
    }
}
