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
        Schema::create('userstatuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('toachieve', 10, 2);
            $table->integer('time');
            $table->string('expansion');
            $table->decimal('limit', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('userstatuses');
    }
};
