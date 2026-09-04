<?php
/**
 * Database Migration & Exporter: MySQL -> PostgreSQL
 * Converts recruitment_tt MySQL database to PostgreSQL schema, indexes, data, and sequence resets.
 *
 * Usage:
 *   php database/migrate_mysql_to_postgres.php [--dump-only] [--pg-host=...] [--pg-port=5432] [--pg-db=recruitment_tt] [--pg-user=postgres] [--pg-pass=...]
 */

$options = getopt('', [
    'dump-only',
    'pg-host:',
    'pg-port:',
    'pg-db:',
    'pg-user:',
    'pg-pass:',
    'pg-sslmode:',
]);

$myHost = '127.0.0.1';
$myPort = '3306';
$myDb   = 'recruitment_tt';
$myUser = 'root';
$myPass = '';

echo "=== RecruitSmart MySQL to PostgreSQL Migration Tool ===\n";
echo "Connecting to MySQL: $myHost:$myPort / $myDb ...\n";

try {
    $myPdo = new PDO("mysql:host=$myHost;port=$myPort;dbname=$myDb;charset=utf8mb4", $myUser, $myPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "MySQL connected successfully.\n";
} catch (Exception $e) {
    echo "Error connecting to MySQL: " . $e->getMessage() . "\n";
    exit(1);
}

// Order of tables to respect foreign keys
$tableOrder = [
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

$outputFile = __DIR__ . '/recruitment_tt_postgres.sql';
$fp = fopen($outputFile, 'w');

fwrite($fp, "-- ========================================================\n");
fwrite($fp, "-- PostgreSQL Database Dump & Schema for recruitment_tt\n");
fwrite($fp, "-- Migrated from MySQL on " . date('Y-m-d H:i:s') . "\n");
fwrite($fp, "-- ========================================================\n\n");

fwrite($fp, "SET statement_timeout = 0;\n");
fwrite($fp, "SET lock_timeout = 0;\n");
fwrite($fp, "SET client_encoding = 'UTF8';\n");
fwrite($fp, "SET standard_conforming_strings = on;\n");
fwrite($fp, "SET check_function_bodies = false;\n");
fwrite($fp, "SET client_min_messages = warning;\n\n");

// Helper to map MySQL type to PG type
function mapTypeToPg($type, $field, $key, $extra) {
    $type = strtolower($type);
    
    // Auto increment primary key
    if (strpos($extra, 'auto_increment') !== false) {
        return 'BIGSERIAL PRIMARY KEY';
    }
    
    if (preg_match('/^tinyint\(1\)/', $type) || $field === 'is_knocked_out' || $field === 'is_read' || $field === 'is_required') {
        return 'BOOLEAN';
    }
    if (preg_match('/^tinyint/', $type)) {
        return 'SMALLINT';
    }
    if (preg_match('/^smallint/', $type)) {
        return 'SMALLINT';
    }
    if (preg_match('/^bigint/', $type)) {
        return 'BIGINT';
    }
    if (preg_match('/^int/', $type)) {
        return 'INTEGER';
    }
    if (preg_match('/^varchar\((\d+)\)/', $type, $m)) {
        return "VARCHAR({$m[1]})";
    }
    if (preg_match('/^char\((\d+)\)/', $type, $m)) {
        return "CHAR({$m[1]})";
    }
    if (preg_match('/^text|mediumtext|longtext/', $type)) {
        return 'TEXT';
    }
    if (preg_match('/^decimal\((\d+),(\d+)\)/', $type, $m)) {
        return "NUMERIC({$m[1]},{$m[2]})";
    }
    if (preg_match('/^date$/', $type)) {
        return 'DATE';
    }
    if (preg_match('/^datetime|timestamp/', $type)) {
        return 'TIMESTAMP(0) WITHOUT TIME ZONE';
    }
    if (preg_match('/^enum\((.+)\)/', $type, $m)) {
        return 'VARCHAR(255)';
    }
    if ($type === 'json') {
        return 'JSONB';
    }
    return 'TEXT';
}

$summary = [];
$totalRowsMigrated = 0;

foreach ($tableOrder as $table) {
    // Check if table exists in MySQL
    $exists = $myPdo->query("SHOW TABLES LIKE '$table'")->rowCount();
    if (!$exists) {
        continue;
    }

    echo "Processing table: $table ...\n";
    fwrite($fp, "-- --------------------------------------------------------\n");
    fwrite($fp, "-- Table structure and data for: \"$table\"\n");
    fwrite($fp, "-- --------------------------------------------------------\n\n");
    fwrite($fp, "DROP TABLE IF EXISTS \"$table\" CASCADE;\n");

    // Fetch column details
    $cols = $myPdo->query("SHOW COLUMNS FROM `$table`")->fetchAll();
    $createLines = [];
    $primaryKeys = [];
    $boolColumns = [];

    foreach ($cols as $col) {
        $field = $col['Field'];
        $type = $col['Type'];
        $null = $col['Null'] === 'NO' ? 'NOT NULL' : 'NULL';
        $default = $col['Default'];
        $extra = $col['Extra'];
        $key = $col['Key'];

        $pgType = mapTypeToPg($type, $field, $key, $extra);
        if ($pgType === 'BOOLEAN') {
            $boolColumns[] = $field;
        }

        $line = "    \"$field\" $pgType";

        if ($pgType !== 'BIGSERIAL PRIMARY KEY') {
            if ($null === 'NOT NULL') {
                $line .= " NOT NULL";
            }
            if ($default !== null) {
                if ($pgType === 'BOOLEAN') {
                    $defVal = ($default == '1' || $default === 'true') ? 'TRUE' : 'FALSE';
                    $line .= " DEFAULT $defVal";
                } elseif (preg_match('/^current_timestamp(\(\))?$/i', $default) || strtolower($default) === 'now()') {
                    $line .= " DEFAULT CURRENT_TIMESTAMP";
                } elseif (is_numeric($default)) {
                    $line .= " DEFAULT $default";
                } else {
                    $line .= " DEFAULT '$default'";
                }
            }
        }

        $createLines[] = $line;

        if ($key === 'PRI' && $pgType !== 'BIGSERIAL PRIMARY KEY') {
            $primaryKeys[] = "\"$field\"";
        }
    }

    if (!empty($primaryKeys)) {
        $createLines[] = "    PRIMARY KEY (" . implode(', ', $primaryKeys) . ")";
    }

    $createTableSql = "CREATE TABLE \"$table\" (\n" . implode(",\n", $createLines) . "\n);\n\n";
    fwrite($fp, $createTableSql);

    // Fetch rows
    $rows = $myPdo->query("SELECT * FROM `$table`")->fetchAll();
    $rowCount = count($rows);
    $totalRowsMigrated += $rowCount;

    if ($rowCount > 0) {
        $columns = array_keys($rows[0]);
        $quotedCols = implode(', ', array_map(fn($c) => "\"$c\"", $columns));

        foreach ($rows as $r) {
            $values = [];
            foreach ($columns as $c) {
                $val = $r[$c];
                if ($val === null) {
                    $values[] = 'NULL';
                } elseif (in_array($c, $boolColumns)) {
                    $values[] = ($val == '1' || $val === 'true' || $val === true) ? 'TRUE' : 'FALSE';
                } elseif (is_numeric($val) && !in_array($c, ['phone', 'tin_no', 'sss_no', 'philhealth_no', 'pagibig_no', 'reference_code'])) {
                    $values[] = $val;
                } else {
                    $escaped = str_replace("'", "''", $val);
                    $values[] = "'$escaped'";
                }
            }
            fwrite($fp, "INSERT INTO \"$table\" ($quotedCols) VALUES (" . implode(', ', $values) . ");\n");
        }
        fwrite($fp, "\n");
    }

    // Sequence reset if id column exists
    $hasIdCol = false;
    foreach ($cols as $c) {
        if ($c['Field'] === 'id' && strpos($c['Extra'], 'auto_increment') !== false) {
            $hasIdCol = true;
            break;
        }
    }

    if ($hasIdCol) {
        $seqName = "{$table}_id_seq";
        fwrite($fp, "SELECT setval('$seqName', COALESCE((SELECT MAX(id) FROM \"$table\"), 1), (SELECT COUNT(*) > 0 FROM \"$table\"));\n\n");
    }

    $summary[] = [
        'table' => $table,
        'count' => $rowCount,
    ];
}

fwrite($fp, "-- Complete: $totalRowsMigrated records exported.\n");
fclose($fp);

$fileKb = round(filesize($outputFile) / 1024, 2);
echo "\n=======================================================\n";
echo "PostgreSQL SQL dump file successfully generated:\n";
echo "File: $outputFile ($fileKb KB, $totalRowsMigrated total records)\n";
echo "=======================================================\n\n";

echo "Summary of Migrated Tables:\n";
foreach ($summary as $s) {
    printf("  - %-25s : %d rows\n", $s['table'], $s['count']);
}

// Live migration if PostgreSQL target options provided
if (isset($options['pg-host'])) {
    $pgHost = $options['pg-host'];
    $pgPort = $options['pg-port'] ?? '5432';
    $pgDb   = $options['pg-db'] ?? 'recruitment_tt';
    $pgUser = $options['pg-user'] ?? 'postgres';
    $pgPass = $options['pg-pass'] ?? '';
    $pgSsl  = $options['pg-sslmode'] ?? 'prefer';

    echo "\nExecuting live import to target PostgreSQL at $pgHost:$pgPort/$pgDb ...\n";
    try {
        $dsn = "pgsql:host=$pgHost;port=$pgPort;dbname=$pgDb;sslmode=$pgSsl";
        $pgPdo = new PDO($dsn, $pgUser, $pgPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        echo "Connected to PostgreSQL! Executing generated dump script...\n";
        $sql = file_get_contents($outputFile);
        $pgPdo->exec($sql);
        echo "Live migration to PostgreSQL completed successfully!\n";
    } catch (Exception $e) {
        echo "Error importing to PostgreSQL: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "\nTo import this dump into your PostgreSQL database:\n";
    echo "  1) Cloud (Render, Supabase, Neon, etc.): Run psql command or copy SQL into web editor:\n";
    echo "     psql \"postgresql://USER:PASSWORD@HOST:PORT/recruitment_tt?sslmode=require\" -f database/recruitment_tt_postgres.sql\n";
    echo "  2) Or run this script with live flags:\n";
    echo "     php database/migrate_mysql_to_postgres.php --pg-host=YOUR_HOST --pg-port=5432 --pg-db=recruitment_tt --pg-user=YOUR_USER --pg-pass=YOUR_PASS --pg-sslmode=require\n";
}
