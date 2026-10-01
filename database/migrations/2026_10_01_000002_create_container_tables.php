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
        // Returnable container kinds a shop lends out (Tub, Can, Bucket...) and their default deposit.
        Schema::create('container_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->string('name');
            $table->decimal('deposit_amount', 12, 2)->default(0.00);
            // Containers the shop owns in total (in shop + with customers). 0 = not tracked.
            $table->integer('total_owned')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // One numbered document per action (give, return, exchange, opening, stock, reversal).
        // Posted entries are never edited — a mistake is undone by a reversal entry.
        Schema::create('container_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->string('entry_number');
            $table->string('type'); // give, return, exchange, opening, stock, reversal
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('set null');
            $table->decimal('deposit_amount', 12, 2)->default(0.00); // deposit taken on containers given
            $table->decimal('refund_amount', 12, 2)->default(0.00);  // deposit given back on containers returned
            $table->decimal('forfeit_amount', 12, 2)->default(0.00); // damage deductions + lost-container deposits kept
            $table->decimal('net_amount', 12, 2)->default(0.00);     // + collected from customer, - paid/adjusted to customer
            $table->string('settlement_method')->default('none');    // cash, upi, bank, due_adjustment, none
            $table->string('note')->nullable();
            $table->dateTime('entry_date');
            $table->foreignId('reversal_of_id')->nullable()->constrained('container_entries')->onDelete('set null');
            $table->dateTime('reversed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['shop_id', 'entry_number']);
            $table->index(['shop_id', 'customer_id']);
        });

        // Entry lines. An "issue" line is a lot of containers with one customer at one deposit rate;
        // return/damaged/lost lines point at the lot they close via lot_id.
        Schema::create('container_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->foreignId('container_entry_id')->constrained('container_entries')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('container_type_id')->constrained('container_types')->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('set null');
            $table->foreignId('lot_id')->nullable()->constrained('container_movements')->onDelete('set null');
            $table->string('kind'); // issue, return, damaged, lost, stock_in, stock_out
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('closed_quantity')->default(0); // issue lots only: returned/damaged/lost so far
            $table->decimal('deposit_per_unit', 12, 2)->default(0.00);
            $table->decimal('amount', 12, 2)->default(0.00);         // issue: deposit held; return/damaged: refund
            $table->decimal('forfeit_amount', 12, 2)->default(0.00); // damaged: deduction; lost: full deposit
            $table->timestamps();

            $table->index(['shop_id', 'customer_id', 'container_type_id', 'kind'], 'container_mov_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_movements');
        Schema::dropIfExists('container_entries');
        Schema::dropIfExists('container_types');
    }
};
