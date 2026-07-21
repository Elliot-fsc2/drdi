<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->unsignedBigInteger('instructor_id')->nullable()->change();
            $table->foreign('instructor_id')
                ->references('id')
                ->on('instructors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->unsignedBigInteger('instructor_id')->nullable(false)->change();
            $table->foreign('instructor_id')
                ->references('id')
                ->on('instructors')
                ->cascadeOnDelete();
        });
    }
};
