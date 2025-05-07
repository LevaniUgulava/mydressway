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
            $table->timestamp("start_data")->nullable();
            $table->timestamp("end_date")->nullable();
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
