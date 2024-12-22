<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('olx_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sku')->unique(); // Унікальний SKU
            $table->string('url')->unique(); // Унікальний URL
            $table->string('name');
            $table->string('image');
            $table->text('description')->nullable();
            $table->char('priceCurrency', 3);
            $table->integer('price');
            $table->timestamp('lastRefreshTime');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olx_products');
    }

};
