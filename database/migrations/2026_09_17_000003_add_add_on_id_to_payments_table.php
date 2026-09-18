<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->change();
            $table->foreignId('add_on_id')->nullable()->after('plan_id')->constrained('add_ons')->nullOnDelete();
            $table->foreignId('user_add_on_id')->nullable()->after('add_on_id')->constrained('user_add_ons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('add_on_id');
            $table->dropConstrainedForeignId('user_add_on_id');
            $table->foreignId('plan_id')->nullable(false)->change();
        });
    }
};
