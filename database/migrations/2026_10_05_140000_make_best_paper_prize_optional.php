<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Best Paper cash prize is optional: an award can be given without one (no prize = NULL).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('best_paper_awards', function (Blueprint $table) {
            $table->decimal('prize_amount', 10, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('best_paper_awards', function (Blueprint $table) {
            $table->decimal('prize_amount', 10, 2)->default(2000)->nullable(false)->change();
        });
    }
};
