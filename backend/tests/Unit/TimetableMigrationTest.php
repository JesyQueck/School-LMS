<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TimetableMigrationTest extends TestCase
{
    use RefreshDatabase;

    private string $migrationFile = '2026_10_27_000001_add_schedules_and_period_type.php';

    private function getMigrationInstance()
    {
        $file = database_path('migrations') . '/' . $this->migrationFile;
        require_once $file;
        return require $file;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
            return ! empty($result);
        }

        $count = DB::select("SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?", [
            DB::getDatabaseName(),
            $table,
            $indexName,
        ]);

        return $count[0]->cnt > 0;
    }

    private function uniqueIndexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
            return ! empty($result);
        }

        $count = DB::select("SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? AND non_unique = 0", [
            DB::getDatabaseName(),
            $table,
            $indexName,
        ]);

        return $count[0]->cnt > 0;
    }

    public function test_migration_has_already_been_applied_by_refresh_database(): void
    {
        $this->assertTrue(Schema::hasColumn('period_configs', 'schedule_name'));
        $this->assertTrue(Schema::hasColumn('period_configs', 'day_of_week'));
        $this->assertTrue(Schema::hasColumn('periods', 'type'));
        $this->assertFalse($this->uniqueIndexExists('period_configs', 'period_configs_academic_session_id_term_id_unique'));
    }

    public function test_up_is_idempotent(): void
    {
        $this->getMigrationInstance()->up();

        $this->assertTrue(Schema::hasColumn('period_configs', 'schedule_name'));
        $this->assertTrue(Schema::hasColumn('period_configs', 'day_of_week'));
        $this->assertTrue(Schema::hasColumn('periods', 'type'));
        $this->assertFalse($this->uniqueIndexExists('period_configs', 'period_configs_academic_session_id_term_id_unique'));
    }

    public function test_down_restores_unique_constraint_and_removes_columns(): void
    {
        $this->getMigrationInstance()->down();

        $this->assertFalse(Schema::hasColumn('period_configs', 'schedule_name'));
        $this->assertFalse(Schema::hasColumn('period_configs', 'day_of_week'));
        $this->assertFalse(Schema::hasColumn('periods', 'type'));
        $this->assertTrue($this->uniqueIndexExists('period_configs', 'period_configs_academic_session_id_term_id_unique'));
    }

    public function test_down_throws_when_duplicates_exist(): void
    {
        $session = DB::table('academic_sessions')->insertGetId([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = DB::table('terms')->insertGetId([
            'academic_session_id' => $session,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-23',
            'is_current' => true,
        ]);

        DB::table('period_configs')->insert([
            'academic_session_id' => $session,
            'term_id' => $term,
            'periods_per_day' => 4,
            'schedule_name' => 'Standard',
            'start_day' => 'Monday',
            'end_day' => 'Friday',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('period_configs')->insert([
            'academic_session_id' => $session,
            'term_id' => $term,
            'periods_per_day' => 5,
            'schedule_name' => 'Extended',
            'start_day' => 'Monday',
            'end_day' => 'Friday',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('duplicate');

        $this->getMigrationInstance()->down();
    }

    public function test_down_does_not_proceed_when_duplicates_exist(): void
    {
        $session = DB::table('academic_sessions')->insertGetId([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = DB::table('terms')->insertGetId([
            'academic_session_id' => $session,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-23',
            'is_current' => true,
        ]);

        DB::table('period_configs')->insert([
            'academic_session_id' => $session,
            'term_id' => $term,
            'periods_per_day' => 4,
            'schedule_name' => 'Standard',
            'start_day' => 'Monday',
            'end_day' => 'Friday',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('period_configs')->insert([
            'academic_session_id' => $session,
            'term_id' => $term,
            'periods_per_day' => 5,
            'schedule_name' => 'Extended',
            'start_day' => 'Monday',
            'end_day' => 'Friday',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->getMigrationInstance()->down();
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('duplicate', $e->getMessage());
        }

        $this->assertTrue(Schema::hasColumn('period_configs', 'schedule_name'));
        $this->assertTrue(Schema::hasColumn('period_configs', 'day_of_week'));
        $this->assertTrue(Schema::hasColumn('periods', 'type'));
        $this->assertFalse($this->uniqueIndexExists('period_configs', 'period_configs_academic_session_id_term_id_unique'));
    }

    public function test_up_allows_multiple_configs_per_term(): void
    {
        $session = DB::table('academic_sessions')->insertGetId([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = DB::table('terms')->insertGetId([
            'academic_session_id' => $session,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-23',
            'is_current' => true,
        ]);

        DB::table('period_configs')->insert([
            'academic_session_id' => $session,
            'term_id' => $term,
            'periods_per_day' => 4,
            'schedule_name' => 'Standard',
            'start_day' => 'Monday',
            'end_day' => 'Friday',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('period_configs')->insert([
            'academic_session_id' => $session,
            'term_id' => $term,
            'periods_per_day' => 5,
            'schedule_name' => 'Extended',
            'start_day' => 'Monday',
            'end_day' => 'Friday',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $count = DB::table('period_configs')
            ->where('academic_session_id', $session)
            ->where('term_id', $term)
            ->count();

        $this->assertEquals(2, $count);
    }

    public function test_up_does_not_touch_existing_records(): void
    {
        $session = DB::table('academic_sessions')->insertGetId([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = DB::table('terms')->insertGetId([
            'academic_session_id' => $session,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-23',
            'is_current' => true,
        ]);

        DB::table('period_configs')->insert([
            'academic_session_id' => $session,
            'term_id' => $term,
            'periods_per_day' => 4,
            'start_day' => 'Monday',
            'end_day' => 'Friday',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $config = DB::table('period_configs')
            ->where('academic_session_id', $session)
            ->where('term_id', $term)
            ->first();

        $this->assertEquals(4, $config->periods_per_day);
        $this->assertEquals('Monday', $config->start_day);
        $this->assertEquals('Friday', $config->end_day);
    }
}
