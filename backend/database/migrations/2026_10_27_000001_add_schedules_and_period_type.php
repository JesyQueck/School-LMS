<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('period_configs')) {
            return;
        }

        if (! Schema::hasColumn('period_configs', 'schedule_name')) {
            Schema::table('period_configs', function (Blueprint $table) {
                $table->string('schedule_name')->nullable()->after('end_day');
            });
        }

        if (! Schema::hasColumn('period_configs', 'day_of_week')) {
            Schema::table('period_configs', function (Blueprint $table) {
                $table->string('day_of_week')->nullable()->after('schedule_name');
            });
        }

        if ($this->uniqueConstraintExists('period_configs', 'period_configs_academic_session_id_term_id_unique')) {
            $this->ensureForeignKeyIndexes('period_configs', 'academic_session_id');
            $this->ensureForeignKeyIndexes('period_configs', 'term_id');

            $this->dropUniqueConstraint('period_configs', 'period_configs_academic_session_id_term_id_unique');
        }

        if (Schema::hasTable('periods') && ! Schema::hasColumn('periods', 'type')) {
            Schema::table('periods', function (Blueprint $table) {
                $table->string('type')->default('teaching')->after('is_break');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('period_configs')) {
            return;
        }

        $duplicateCheck = DB::table('period_configs')
            ->select('academic_session_id', 'term_id')
            ->groupBy('academic_session_id', 'term_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateCheck) {
            throw new \RuntimeException(
                'Cannot roll back: duplicate (academic_session_id, term_id) records exist in period_configs. ' .
                'The unique constraint cannot be restored. Remove or consolidate duplicate schedules first.'
            );
        }

        $uniqueExists = $this->uniqueConstraintExists('period_configs', 'period_configs_academic_session_id_term_id_unique');

        if (! $uniqueExists) {
            $this->createUniqueConstraint('period_configs', 'period_configs_academic_session_id_term_id_unique', ['academic_session_id', 'term_id']);
        }

        $this->dropForeignKeyIndexes('period_configs', 'academic_session_id');
        $this->dropForeignKeyIndexes('period_configs', 'term_id');

        $columnsToDrop = [];
        if (Schema::hasColumn('period_configs', 'schedule_name')) {
            $columnsToDrop[] = 'schedule_name';
        }
        if (Schema::hasColumn('period_configs', 'day_of_week')) {
            $columnsToDrop[] = 'day_of_week';
        }
        if ($columnsToDrop) {
            Schema::table('period_configs', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        if (Schema::hasTable('periods') && Schema::hasColumn('periods', 'type')) {
            Schema::table('periods', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }

    private function ensureForeignKeyIndexes(string $table, string $column): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        $dbName = DB::getDatabaseName();

        $leadingIndex = DB::select(
            "SELECT index_name FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND column_name = ? AND seq_in_index = 1
             AND index_name != ? AND non_unique = 1
             GROUP BY index_name",
            [$dbName, $table, $column, 'period_configs_academic_session_id_term_id_unique']
        );

        if (! empty($leadingIndex)) {
            return;
        }

        $indexName = $table . '_' . $column . '_index';
        $quotedIndex = '`' . str_replace('`', '``', $indexName) . '`';
        $quotedColumn = '`' . str_replace('`', '``', $column) . '`';
        $quotedTable = '`' . str_replace('`', '``', $table) . '`';

        DB::statement("ALTER TABLE {$quotedTable} ADD INDEX {$quotedIndex} ({$quotedColumn})");
    }

    private function dropForeignKeyIndexes(string $table, string $column): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        $indexName = $table . '_' . $column . '_index';

        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        $quotedTable = '`' . str_replace('`', '``', $table) . '`';
        $quotedIndex = '`' . str_replace('`', '``', $indexName) . '`';

        DB::statement("DROP INDEX {$quotedIndex} ON {$quotedTable}");
    }

    private function dropUniqueConstraint(string $table, string $indexName): void
    {
        if (! $this->uniqueConstraintExists($table, $indexName)) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement("DROP INDEX `{$indexName}`");
            return;
        }

        $quotedTable = '`' . str_replace('`', '``', $table) . '`';
        $quotedIndex = '`' . str_replace('`', '``', $indexName) . '`';

        DB::statement("ALTER TABLE {$quotedTable} DROP INDEX {$quotedIndex}");
    }

    private function uniqueConstraintExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
            return ! empty($result);
        }

        $dbName = DB::getDatabaseName();
        $stmt = DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? AND non_unique = 0",
            [$dbName, $table, $indexName]
        );
        return $stmt[0]->cnt > 0;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
            return ! empty($result);
        }

        $dbName = DB::getDatabaseName();
        $stmt = DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$dbName, $table, $indexName]
        );
        return $stmt[0]->cnt > 0;
    }

    private function createUniqueConstraint(string $table, string $indexName, array $columns): void
    {
        $driver = DB::getDriverName();

        $quotedColumns = array_map(fn ($c) => '`' . str_replace('`', '``', $c) . '`', $columns);
        $columnListQuoted = implode(', ', $quotedColumns);

        if ($driver === 'sqlite') {
            DB::statement("CREATE UNIQUE INDEX `{$indexName}` ON {$table} ({$columnListQuoted})");
            return;
        }

        $quotedTable = '`' . str_replace('`', '``', $table) . '`';

        try {
            DB::statement("ALTER TABLE {$quotedTable} ADD CONSTRAINT {$indexName} UNIQUE ({$columnListQuoted})");
        } catch (\Exception $e) {
            $quotedIndex = '`' . str_replace('`', '``', $indexName) . '`';
            DB::statement("CREATE UNIQUE INDEX {$quotedIndex} ON {$table} ({$columnListQuoted})");
        }
    }
};
