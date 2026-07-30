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
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('name');
            $table->date('date_of_birth')->nullable()->after('mobile');
            $table->string('gender', 20)->nullable()->after('date_of_birth');
            $table->string('language', 10)->default('en')->after('status');
            $table->string('currency', 10)->default('INR')->after('language');
            $table->string('date_format', 20)->default('DD/MM/YYYY')->after('currency');
            $table->string('time_format', 10)->default('12h')->after('date_format');
            $table->string('theme', 10)->default('system')->after('time_format');
            $table->json('notification_preferences')->nullable()->after('theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'display_name',
                'date_of_birth',
                'gender',
                'language',
                'currency',
                'date_format',
                'time_format',
                'theme',
                'notification_preferences',
            ]);
        });
    }
};
