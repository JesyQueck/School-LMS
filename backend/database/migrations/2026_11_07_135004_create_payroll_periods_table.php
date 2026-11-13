<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'payroll_periods';
        $driver = DB::getDriverName();

        if (!Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->date('start_date');
                $table->date('end_date');
                $table->date('pay_date');
                $table->string('status')->default('draft')->comment('draft, processed, paid, locked');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['status']);
                $table->index(['start_date', 'end_date']);
                $table->index(['created_by']);
            });

            return;
        }

        // Safe in-place reconciliation for an existing table.
        $columns = $this->existingColumns($table, $driver);

        $requiredColumns = [
            'id',
            'name',
            'start_date',
            'end_date',
            'pay_date',
            'status',
            'created_by',
        ];

        foreach ($requiredColumns as $column) {
            if (!isset($columns[$column])) {
                throw new \RuntimeException(
                    "Cannot reconcile payroll_periods migration: required column '{$column}' is missing from the existing table."
                );
            }
        }

        $columnTypes = $this->existingColumnTypes($table, $driver);

        // Validate created_by column type is integer-based before altering.
        $createdByType = $columnTypes['created_by'] ?? null;
        if ($createdByType !== null && ! $this->isIntegerType($createdByType)) {
            throw new \RuntimeException(
                "Cannot reconcile payroll_periods migration: 'created_by' column has unexpected type '{$createdByType}'. Expected an integer type."
            );
        }

        // Safely alter created_by to nullable if it is not already.
        if (! $this->isColumnNullable($table, 'created_by', $driver)) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->change();
            });
        }

        // Reconcile status column type if needed.
        Schema::table($table, function (Blueprint $table) use ($columnTypes) {
            $statusType = $columnTypes['status'] ?? null;
            if ($statusType === null || ! str_contains((string) $statusType, 'varchar')) {
                $table->string('status')->default('draft')->comment('draft, processed, paid, locked')->change();
            }
        });

        // Add missing indexes only if absent.
        $indexes = $this->existingIndexes($table, $driver);

        $indexDefinitions = [
            'payroll_periods_status_index' => ['status'],
            'payroll_periods_start_date_end_date_index' => ['start_date', 'end_date'],
            'payroll_periods_created_by_index' => ['created_by'],
        ];

        foreach ($indexDefinitions as $indexName => $columns) {
            if (!isset($indexes[$indexName])) {
                Schema::table($table, function (Blueprint $table) use ($indexName, $columns) {
                    $table->index($columns)->name($indexName);
                });
            }
        }

        // Add the created_by foreign key only if absent, after checking for orphaned references.
        $foreignKeys = $this->existingForeignKeys($table, $driver);

        $hasCreatedByFk = false;
        foreach ($foreignKeys as $fk) {
            if ($fk['column_name'] === 'created_by') {
                $hasCreatedByFk = true;
                break;
            }
        }

        if (! $hasCreatedByFk) {
            $orphanedCount = DB::table($table)
                ->whereNotNull('created_by')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.id', 'payroll_periods.created_by');
                })
                ->count();

            if ($orphanedCount > 0) {
                throw new \RuntimeException(
                    "Cannot reconcile payroll_periods migration: {$orphanedCount} row(s) reference a non-existent user in 'created_by'. Resolve orphaned references before adding the foreign key."
                );
            }

            Schema::table($table, function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }

    /**
     * Get the existing column names for a table.
     *
     * @return array<string, bool>
     */
    protected function existingColumns(string $table, string $driver): array
    {
        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA table_info(`{$table}`)");

            return collect($rows)->mapWithKeys(fn ($row) => [$row->name => true])->all();
        }

        $rows = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$table]);

        return collect($rows)->mapWithKeys(fn ($row) => [$row->COLUMN_NAME => true])->all();
    }

    /**
     * Get the existing column data types for a table.
     *
     * @return array<string, string>
     */
    protected function existingColumnTypes(string $table, string $driver): array
    {
        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA table_info(`{$table}`)");

            return collect($rows)->mapWithKeys(fn ($row) => [$row->name => $row->type])->all();
        }

        $rows = DB::select("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$table]);

        return collect($rows)->mapWithKeys(fn ($row) => [$row->COLUMN_NAME => $row->DATA_TYPE])->all();
    }

    /**
     * Determine whether a column is nullable.
     */
    protected function isColumnNullable(string $table, string $column, string $driver): bool
    {
        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA table_info(`{$table}`)");
            $found = collect($rows)->firstWhere('name', $column);

            return $found !== null && (int) ($found->notnull ?? 1) === 0;
        }

        $row = DB::select("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1", [$table, $column]);

        return !empty($row) && strtolower($row[0]->IS_NULLABLE) === 'yes';
    }

    /**
     * Determine whether a column type is integer-based.
     */
    protected function isIntegerType(?string $type): bool
    {
        if ($type === null) {
            return false;
        }

        return (bool) preg_match('/int/i', $type);
    }

    /**
     * Get existing index names for a table.
     *
     * @return array<string, bool>
     */
    protected function existingIndexes(string $table, string $driver): array
    {
        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list(`{$table}`)");

            return collect($rows)->mapWithKeys(fn ($row) => [$row->name => true])->all();
        }

        $rows = DB::select("SHOW INDEX FROM `{$table}`");

        return collect($rows)->mapWithKeys(fn ($row) => [$row->Key_name => true])->all();
    }

    /**
     * Get existing foreign keys for a table.
     *
     * @return array<string, mixed>
     */
    protected function existingForeignKeys(string $table, string $driver): array
    {
        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA foreign_key_list(`{$table}`)");

            return collect($rows)->map(fn ($row) => [
                'column_name' => $row->from,
                'constraint_name' => $row->id ?? null,
                'referenced_table' => $row->table,
                'referenced_column' => $row->to,
            ])->values()->all();
        }

        $rows = DB::select("
            SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table]);

        return collect($rows)->map(fn ($row) => [
            'column_name' => $row->COLUMN_NAME,
            'constraint_name' => $row->CONSTRAINT_NAME,
            'referenced_table' => $row->REFERENCED_TABLE_NAME,
            'referenced_column' => $row->REFERENCED_COLUMN_NAME,
        ])->values()->all();
    }
};
