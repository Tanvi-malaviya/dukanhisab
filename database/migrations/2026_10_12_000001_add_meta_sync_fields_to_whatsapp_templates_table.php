<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Templates can now be submitted to Meta from the admin panel and synced back. These columns
     * mirror Meta's side: its template id, review status (PENDING / APPROVED / REJECTED / PAUSED /
     * DISABLED ...), category and the body text Meta holds. A null meta_status means the row was
     * never submitted or synced (rows added before this feature) — those keep sending as before.
     */
    public function up(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->string('meta_template_id')->nullable()->after('meta_language_code');
            $table->string('meta_status', 30)->nullable()->after('meta_template_id');
            $table->string('meta_category', 30)->nullable()->after('meta_status');
            $table->string('meta_rejected_reason')->nullable()->after('meta_category');
            $table->text('meta_body')->nullable()->after('meta_rejected_reason');
            $table->timestamp('meta_synced_at')->nullable()->after('meta_body');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->dropColumn(['meta_template_id', 'meta_status', 'meta_category', 'meta_rejected_reason', 'meta_body', 'meta_synced_at']);
        });
    }
};
