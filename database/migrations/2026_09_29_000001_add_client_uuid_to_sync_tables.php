<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Offline clients tag every record they create with a client-generated UUID.
 * If a create is retried (response lost, batch re-sent with a different shape),
 * the server finds the record by (shop_id, client_uuid) instead of creating a duplicate.
 */
return new class extends Migration
{
    private array $tables = ['products', 'customers', 'suppliers', 'categories', 'sales', 'purchases', 'cash_books'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'client_uuid')) {
                continue; // Already applied in an earlier partial run of this migration.
            }
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->string('client_uuid', 64)->nullable()->after('id');
                $t->unique(['shop_id', 'client_uuid'], "{$table}_shop_client_uuid_unique");
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropUnique("{$table}_shop_client_uuid_unique");
                $t->dropColumn('client_uuid');
            });
        }
    }
};
