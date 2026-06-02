<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('seo_settings')) {
            return;
        }

        $hasRobots = Schema::hasColumn('seo_settings', 'robots_txt');
        $hasContent = Schema::hasColumn('seo_settings', 'content');

        if (!$hasRobots || $hasContent) {
            return;
        }

        Schema::table('seo_settings', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('company_id');
        });

        DB::table('seo_settings')->update([
            'content' => DB::raw('robots_txt'),
        ]);

        Schema::table('seo_settings', function (Blueprint $table) {
            $table->dropColumn('robots_txt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('seo_settings')) {
            return;
        }

        $hasRobots = Schema::hasColumn('seo_settings', 'robots_txt');
        $hasContent = Schema::hasColumn('seo_settings', 'content');

        if ($hasRobots || !$hasContent) {
            return;
        }

        Schema::table('seo_settings', function (Blueprint $table) {
            $table->longText('robots_txt')->nullable()->after('company_id');
        });

        DB::table('seo_settings')->update([
            'robots_txt' => DB::raw('content'),
        ]);

        Schema::table('seo_settings', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};

