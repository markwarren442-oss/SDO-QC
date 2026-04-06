<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportFromSqliteSeeder extends Seeder
{
    /**
     * Import all existing data from the SQLite database into MySQL.
     * Run: php artisan db:seed --class=ImportFromSqliteSeeder
     */
    public function run(): void
    {
        $sqliteDb = 'c:/xampp/htdocs/SDO-QC/attendance_data.db';

        if (!file_exists($sqliteDb)) {
            $this->command->error("SQLite database not found at: $sqliteDb");
            return;
        }

        $sqlite = new \PDO("sqlite:$sqliteDb");
        $sqlite->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Disable foreign key checks for import
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'users',
            'employees',
            'daily_attendance',
            'absence_reasons',
            'absences',
            'late_minutes',
            'undertime_minutes',
            'special_days',
            'forms',
            'audit_logs',
            'activity_logs',
            'login_logs',
            'correction_requests',
            'monthly_daw',
            'notes',
            'qr_tokens',
            'settings'
        ];

        foreach ($tables as $table) {
            try {
                $rows = $sqlite->query("SELECT * FROM $table")->fetchAll(\PDO::FETCH_ASSOC);
                if (empty($rows)) {
                    $this->command->info("  ⏭  $table — no data");
                    continue;
                }

                // Truncate existing data for clean re-import
                DB::table($table)->truncate();

                // Insert in chunks of 100
                $chunks = array_chunk($rows, 100);
                $count = 0;
                foreach ($chunks as $chunk) {
                    DB::table($table)->insert($chunk);
                    $count += count($chunk);
                }
                $this->command->info("  ✅ $table — $count rows imported");
            } catch (\Exception $e) {
                $this->command->warn("  ⚠  $table — " . $e->getMessage());
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info("\n🎉 Data import complete!");
    }
}
