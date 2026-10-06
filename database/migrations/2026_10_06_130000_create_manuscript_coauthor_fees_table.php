<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Co-author surcharge per content category: each of the 1st and 2nd co-authors adds `first_two_fee`,
 * every further co-author adds `additional_fee`. Existing categories get the published grid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manuscript_coauthor_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_category_id')->unique()->constrained('manuscript_content_categories')->cascadeOnDelete();
            $table->decimal('first_two_fee', 10, 2)->default(0);
            $table->decimal('additional_fee', 10, 2)->default(0);
            $table->timestamps();
        });

        foreach (DB::table('manuscript_content_categories')->get(['id', 'name']) as $category) {
            if ($rates = \Database\Seeders\CatalogueSeeder::coAuthorRatesFor($category->name)) {
                DB::table('manuscript_coauthor_fees')->insert([
                    'content_category_id' => $category->id,
                    'first_two_fee' => $rates[0],
                    'additional_fee' => $rates[1],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('manuscript_coauthor_fees');
    }
};
