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
        Schema::table('shops', function (Blueprint $table) {
            $table->string('signature')->nullable()->after('logo');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('pincode', 10)->nullable()->after('state');
            $table->string('invoice_prefix', 20)->nullable()->after('gst_number');
            $table->string('currency', 10)->default('INR')->after('invoice_prefix');
            $table->string('upi_id')->nullable()->after('currency');
            $table->text('bank_details')->nullable()->after('upi_id');
            $table->text('invoice_footer')->nullable()->after('bank_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'signature',
                'city',
                'state',
                'pincode',
                'invoice_prefix',
                'currency',
                'upi_id',
                'bank_details',
                'invoice_footer',
            ]);
        });
    }
};
