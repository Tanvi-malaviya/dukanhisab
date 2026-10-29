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
        Schema::create('system_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10)->index();
            $table->string('key', 191)->index();
            $table->mediumText('value')->nullable();
            $table->timestamps();

            $table->unique(['locale', 'key'], 'system_translations_locale_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_translations');
    }
};
