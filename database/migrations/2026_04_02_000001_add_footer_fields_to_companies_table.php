<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Stores Upload.id for the footer logo image (same approach as `logo`)
            $table->string('footer_logo_image')->nullable();
            // Short footer description text
            $table->longText('short_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['footer_logo_image', 'short_description']);
        });
    }
};

