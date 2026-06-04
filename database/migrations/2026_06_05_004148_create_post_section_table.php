<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_section', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->primary(['post_id', 'section_id']);
        });

        DB::table('posts')
            ->whereNotNull('section_id')
            ->chunkById(100, function (object $posts): void {
                $rows = collect($posts)->map(fn (object $post): array => [
                    'post_id' => $post->id,
                    'section_id' => $post->section_id,
                ])->toArray();

                DB::table('post_section')->insert($rows);
            });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('section_id')
                ->nullable()
                ->after('target_type')
                ->constrained('sections')
                ->cascadeOnDelete();
        });

        DB::table('posts')
            ->whereNull('section_id')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('post_section')->whereColumn('post_section.post_id', 'posts.id'))
            ->chunkById(100, function (object $posts): void {
                collect($posts)->each(function (object $post): void {
                    $firstSectionId = DB::table('post_section')
                        ->where('post_id', $post->id)
                        ->value('section_id');

                    if ($firstSectionId) {
                        DB::table('posts')
                            ->where('id', $post->id)
                            ->update(['section_id' => $firstSectionId]);
                    }
                });
            });

        Schema::dropIfExists('post_section');
    }
};
