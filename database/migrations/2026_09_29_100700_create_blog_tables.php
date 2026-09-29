<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * schema.sql Section 6 — blogs, categories, tags and comments (SOW A.05–A.08, B.05, C.02).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag_name', 80);
            $table->string('slug', 100)->unique();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->restrictOnDelete();
            $table->string('blog_title');
            $table->string('slug', 280)->unique();
            $table->foreignId('category_id')->index()->constrained('blog_categories')->restrictOnDelete();
            $table->string('author_name', 150);
            $table->string('featured_image')->nullable();
            $table->string('excerpt', 500);
            $table->longText('content');
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('og_image')->nullable();
            // "Pending" added: author posts awaiting approval when the
            // "author blog posts require approval" setting is on.
            $table->enum('status', ['Published', 'Draft', 'Pending'])->default('Draft');
            $table->date('publish_date');
            $table->boolean('featured_post')->default(false);
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();

            $table->index(['status', 'publish_date']);
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE blogs ADD FULLTEXT ftx_blog_title (blog_title)');
        }

        Schema::create('blog_tag_pivot', function (Blueprint $table) {
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->index()->constrained()->cascadeOnDelete();

            $table->primary(['blog_id', 'blog_tag_id']);
        });

        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->index()->constrained('blog_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('email', 190);
            $table->text('comment');
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
        Schema::dropIfExists('blog_tag_pivot');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_categories');
    }
};
