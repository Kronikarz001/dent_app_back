<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Older pivot tables (pre-21.08 RBAC rewrite) were created without
     * cascadeOnDelete()/unique() — deleting a parent row would throw an FK
     * violation instead of cleaning up, and nothing stopped duplicate
     * assignment rows. Existing duplicates are collapsed before the unique
     * index is added, so this is safe to run against data that already
     * accumulated dupes.
     *
     * @return void
     */
    public function up(): void
    {
        $this->dedupePivot('users_job_positions', ['user_uuid', 'job_position_uuid']);
        Schema::table('users_job_positions', function (Blueprint $table) {
            $table->dropForeign(['user_uuid']);
            $table->dropForeign(['job_position_uuid']);
            $table->foreign('user_uuid')->references('uuid')->on('users')->cascadeOnDelete();
            $table->foreign('job_position_uuid')->references('uuid')->on('job_positions')->cascadeOnDelete();
            $table->unique(['user_uuid', 'job_position_uuid'], 'users_job_positions_unique');
        });

        $this->dedupePivot('calendar_users', ['calendar_uuid', 'userable_id', 'userable_type']);
        Schema::table('calendar_users', function (Blueprint $table) {
            $table->dropForeign(['calendar_uuid']);
            $table->foreign('calendar_uuid')->references('uuid')->on('calendars')->cascadeOnDelete();
            $table->unique(['calendar_uuid', 'userable_id', 'userable_type'], 'calendar_users_unique_assignment');
            $table->index(['userable_type', 'userable_id']);
        });

        $this->dedupePivot('calendars_dental_examinations', ['calendar_uuid', 'dental_examination_uuid']);
        Schema::table('calendars_dental_examinations', function (Blueprint $table) {
            $table->dropForeign(['calendar_uuid']);
            $table->dropForeign(['dental_examination_uuid']);
            $table->foreign('calendar_uuid')->references('uuid')->on('calendars')->cascadeOnDelete();
            $table->foreign('dental_examination_uuid')->references('uuid')->on('dental_examinations')->cascadeOnDelete();
            $table->unique(['calendar_uuid', 'dental_examination_uuid'], 'calendars_dental_examinations_unique');
        });

        $this->dedupePivot('dental_examinations_materials', ['dental_examination_uuid', 'material_uuid']);
        Schema::table('dental_examinations_materials', function (Blueprint $table) {
            $table->dropForeign(['dental_examination_uuid']);
            $table->dropForeign(['material_uuid']);
            $table->foreign('dental_examination_uuid')->references('uuid')->on('dental_examinations')->cascadeOnDelete();
            $table->foreign('material_uuid')->references('uuid')->on('materials')->cascadeOnDelete();
            $table->unique(['dental_examination_uuid', 'material_uuid'], 'dental_examinations_materials_unique');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['message_group_uuid']);
            $table->foreign('message_group_uuid')->references('uuid')->on('message_groups')->cascadeOnDelete();
        });

        $this->dedupePermissionAssignments();
        Schema::table('permission_assignments', function (Blueprint $table) {
            $table->unique(
                ['grantable_type', 'grantable_id', 'assignable_type', 'assignable_id'],
                'permission_assignments_unique_grant'
            );
        });

        Schema::table('calendars', function (Blueprint $table) {
            $table->index(['type', 'date']);
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropIndex(['type', 'date']);
        });

        Schema::table('permission_assignments', function (Blueprint $table) {
            $table->dropUnique('permission_assignments_unique_grant');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['message_group_uuid']);
            $table->foreign('message_group_uuid')->references('uuid')->on('message_groups');
        });

        Schema::table('dental_examinations_materials', function (Blueprint $table) {
            $table->dropForeign(['dental_examination_uuid']);
            $table->dropForeign(['material_uuid']);
            $table->dropUnique('dental_examinations_materials_unique');
            $table->foreign('dental_examination_uuid')->references('uuid')->on('dental_examinations');
            $table->foreign('material_uuid')->references('uuid')->on('materials');
        });

        Schema::table('calendars_dental_examinations', function (Blueprint $table) {
            $table->dropForeign(['calendar_uuid']);
            $table->dropForeign(['dental_examination_uuid']);
            $table->dropUnique('calendars_dental_examinations_unique');
            $table->foreign('calendar_uuid')->references('uuid')->on('calendars');
            $table->foreign('dental_examination_uuid')->references('uuid')->on('dental_examinations');
        });

        Schema::table('calendar_users', function (Blueprint $table) {
            $table->dropIndex(['userable_type', 'userable_id']);
            $table->dropForeign(['calendar_uuid']);
            $table->dropUnique('calendar_users_unique_assignment');
            $table->foreign('calendar_uuid')->references('uuid')->on('calendars');
        });

        Schema::table('users_job_positions', function (Blueprint $table) {
            $table->dropForeign(['user_uuid']);
            $table->dropForeign(['job_position_uuid']);
            $table->dropUnique('users_job_positions_unique');
            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('job_position_uuid')->references('uuid')->on('job_positions');
        });
    }

    /**
     * Pivot rows here carry no identity beyond the key columns + assigned_at,
     * so duplicates are collapsed by deleting the whole group and inserting
     * a single row back — portable across MySQL/PostgreSQL without relying
     * on a primary key to target individual rows.
     *
     * @param string $table
     * @param array $columns
     * @return void
     */
    private function dedupePivot(string $table, array $columns): void
    {
        $duplicateGroups = DB::table($table)
            ->select($columns)
            ->groupBy($columns)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $groupArray = (array) $group;

            $earliestAssignedAt = DB::table($table)->where($groupArray)->min('assigned_at');

            DB::table($table)->where($groupArray)->delete();
            DB::table($table)->insert($groupArray + ['assigned_at' => $earliestAssignedAt]);
        }
    }

    /**
     * @return void
     */
    private function dedupePermissionAssignments(): void
    {
        $columns = ['grantable_type', 'grantable_id', 'assignable_type', 'assignable_id'];

        $duplicateGroups = DB::table('permission_assignments')
            ->select($columns)
            ->groupBy($columns)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $groupArray = (array) $group;

            $keepUuid = DB::table('permission_assignments')->where($groupArray)->min('uuid');

            DB::table('permission_assignments')->where($groupArray)->where('uuid', '!=', $keepUuid)->delete();
        }
    }
};
