<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('user_olx_product_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('olx_product_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'olx_product_id'], 'user_product_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_olx_product_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['olx_product_id']);
            $table->dropUnique('user_product_unique');
        });

        Schema::dropIfExists('user_olx_product_subscriptions');
    }

};
