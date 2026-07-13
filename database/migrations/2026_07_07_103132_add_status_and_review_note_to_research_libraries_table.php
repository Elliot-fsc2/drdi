<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('research_libraries', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('file_path');
            $table->text('review_note')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('research_libraries', function (Blueprint $table) {
            $table->dropColumn(['status', 'review_note']);
        });
    }
};
