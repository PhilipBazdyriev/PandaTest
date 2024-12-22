<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('olx_products', function (Blueprint $table) {
            $table->dropColumn('lastRefreshTime');
            $table->renameColumn('priceCurrency', 'price_currency');
        });
    }

    public function down(): void
    {
        Schema::table('olx_products', function (Blueprint $table) {
            $table->timestamp('lastRefreshTime')->nullable();
            $table->renameColumn('price_currency', 'priceCurrency');
        });
    }
};
