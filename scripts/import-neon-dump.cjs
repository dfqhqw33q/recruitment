const fs = require('node:fs');
const path = require('node:path');
const { Client } = require('pg');

const connectionString = process.env.NEON_DATABASE_URL || process.env.DB_URL;
const dumpPath = path.resolve(__dirname, '..', 'database', 'recruitment_tt_postgres.sql');

if (!connectionString) {
  console.error('Set NEON_DATABASE_URL or DB_URL before running this script.');
  process.exit(1);
}

if (process.argv[2] !== '--confirm') {
  console.error('This import replaces tables from the SQL dump. Re-run with --confirm to continue.');
  process.exit(1);
}

if (!fs.existsSync(dumpPath)) {
  console.error(`Database dump not found: ${dumpPath}`);
  process.exit(1);
}

const sql = fs.readFileSync(dumpPath, 'utf8');
const client = new Client({
  connectionString,
  connectionTimeoutMillis: 15000,
});

(async () => {
  console.log(`Importing ${path.basename(dumpPath)}...`);
  await client.connect();
  await client.query(sql);

  const result = await client.query(
    "select count(*)::int as tables from pg_tables where schemaname = 'public'"
  );

  console.log(`Import complete. Public tables: ${result.rows[0].tables}`);
  await client.end();
})().catch(async (error) => {
  console.error(`Import failed: ${error.message}`);
  try {
    await client.end();
  } catch {}
  process.exit(1);
});
