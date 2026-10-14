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
        Schema::table('invoice_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_configs', 'text_color')) {
                $table->string('text_color', 10)->default('#FFFFFF')->after('theme_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_configs', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_configs', 'text_color')) {
                $table->dropColumn('text_color');
            }
        });
    }
};
