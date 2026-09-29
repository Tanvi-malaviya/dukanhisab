<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets the admin panel mark a subscription, add-on or shop as manually granted (no payment,
 * no purchase limit applied) and show that on its own screens, while the shop-owner app/web
 * never sees the difference — these columns are hidden on the models that serialize to it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('granted_by_admin')->default(false)->after('trial_ends_at');
            $table->string('admin_note')->nullable()->after('granted_by_admin');
        });
        Schema::table('user_add_ons', function (Blueprint $table) {
            $table->boolean('granted_by_admin')->default(false)->after('razorpay_subscription_id');
            $table->string('admin_note')->nullable()->after('granted_by_admin');
        });
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('added_by_admin')->default(false)->after('active_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['granted_by_admin', 'admin_note']);
        });
        Schema::table('user_add_ons', function (Blueprint $table) {
            $table->dropColumn(['granted_by_admin', 'admin_note']);
        });
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('added_by_admin');
        });
    }
};
