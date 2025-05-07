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
        Schema::create('temporary_orders', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id")->nullable();
            $table->string("guest_token")->nullable();
            $table->integer("quantity");
            $table->string("name");
            $table->integer("product_id");
            $table->string("size");
            $table->string("color");
            $table->string("type");
            $table->decimal("retail_price", 8, 2);
            $table->decimal("total_price", 8, 2);
            $table->index('guest_token');

            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_orders');
    }
};
