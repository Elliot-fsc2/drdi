<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thesis_rates', function (Blueprint $table) {
            $table->string('personnel_role')->nullable()->after('type');
            $table->string('panelist_role')->nullable()->after('personnel_role');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->integer('panel_fee')->default(0)->after('panelists');
        });

        Schema::table('group_fees', function (Blueprint $table) {
            $table->integer('panel_fee_total')->default(0)->after('honorarium_total');
        });
    }

    public function down(): void
    {
        Schema::table('thesis_rates', function (Blueprint $table) {
            $table->dropColumn(['personnel_role', 'panelist_role']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('panel_fee');
        });

        Schema::table('group_fees', function (Blueprint $table) {
            $table->dropColumn('panel_fee_total');
        });
    }
};
