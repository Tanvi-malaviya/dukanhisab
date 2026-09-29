<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This environment's `categories`, `support_tickets` and `advertisements` tables are missing even
 * though the migrations table marks their creating migrations (and, for categories, the later
 * soft-deletes migration) as already run — most likely a partial restore dropped them without
 * touching the migrations table. Recreates each with its full final schema, but only if it is
 * genuinely missing, so this is a no-op anywhere the tables already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
                $table->string('name');
                $table->string('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('subject');
                $table->text('message');
                $table->string('screenshot')->nullable();
                $table->string('status')->default('open');
                $table->text('admin_reply')->nullable();
                $table->timestamp('replied_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('advertisements')) {
            Schema::create('advertisements', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->string('title');
                $table->string('image_url')->nullable();
                $table->string('target_url')->nullable();
                $table->text('script_code')->nullable();
                $table->string('status')->default('active');
                $table->integer('clicks')->default(0);
                $table->integer('views')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Intentionally left as a no-op: this migration only repairs tables another migration
        // already owns dropping, and only ever creates what was found missing.
    }
};
