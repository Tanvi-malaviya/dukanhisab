<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix customers table
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'shop_id')) {
                $table->foreignId('shop_id')->after('id')->constrained('shops')->onDelete('cascade');
            }
            if (!Schema::hasColumn('customers', 'name')) {
                $table->string('name')->after('shop_id');
            }
            if (!Schema::hasColumn('customers', 'mobile')) {
                $table->string('mobile')->nullable()->after('name');
            }
            if (!Schema::hasColumn('customers', 'email')) {
                $table->string('email')->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('customers', 'due_amount')) {
                $table->decimal('due_amount', 12, 2)->default(0.00)->after('email');
            }
        });

        // Fix suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'shop_id')) {
                $table->foreignId('shop_id')->after('id')->constrained('shops')->onDelete('cascade');
            }
            if (!Schema::hasColumn('suppliers', 'name')) {
                $table->string('name')->after('shop_id');
            }
            if (!Schema::hasColumn('suppliers', 'mobile')) {
                $table->string('mobile')->nullable()->after('name');
            }
            if (!Schema::hasColumn('suppliers', 'email')) {
                $table->string('email')->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('suppliers', 'due_amount')) {
                $table->decimal('due_amount', 12, 2)->default(0.00)->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropColumn(['shop_id', 'name', 'mobile', 'email', 'due_amount']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropColumn(['shop_id', 'name', 'mobile', 'email', 'due_amount']);
        });
    }
};
