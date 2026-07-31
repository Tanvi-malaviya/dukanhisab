<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add active_plan_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('active_plan_id')->nullable()->after('status')->constrained('subscription_plans')->onDelete('set null');
        });

        // 2. Add user_id to subscriptions table and make shop_id nullable
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
        });

        // Make shop_id nullable on subscriptions table if needed
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id')->nullable()->change();
        });

        // 3. Migrate existing subscriptions: populate user_id from shops table
        $shops = DB::table('shops')->get();
        foreach ($shops as $shop) {
            if ($shop->owner_id) {
                // Update subscriptions for this shop to set user_id
                DB::table('subscriptions')
                    ->where('shop_id', $shop->id)
                    ->whereNull('user_id')
                    ->update(['user_id' => $shop->owner_id]);

                // Update owner user's active_plan_id from shop's active_plan_id
                if ($shop->active_plan_id) {
                    DB::table('users')
                        ->where('id', $shop->owner_id)
                        ->whereNull('active_plan_id')
                        ->update(['active_plan_id' => $shop->active_plan_id]);
                }
            }
        }

        // Set default free plan for any users without active_plan_id
        $freePlan = DB::table('subscription_plans')->where('slug', 'free')->first();
        if ($freePlan) {
            DB::table('users')->whereNull('active_plan_id')->update(['active_plan_id' => $freePlan->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_plan_id']);
            $table->dropColumn('active_plan_id');
        });
    }
};
