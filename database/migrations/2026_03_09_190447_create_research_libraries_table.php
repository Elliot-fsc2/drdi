<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('research_libraries', function (Blueprint $table) {
      $table->id();
      $table->foreignId('group_id')->constrained()->onDelete('cascade');
      $table->string('academic_year');
      $table->string('title'); // slug
      $table->text('abstract');
      $table->string('file_path')->nullable();
      $table->boolean('is_published')->default(false);
      $table->dateTime('published_at')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('research_libraries');
  }
};
