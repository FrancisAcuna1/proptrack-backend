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
        Schema::create('apartment_inclusions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apartment_id');
            // $table->foreign('apartment_id')->references('id')->on('apartment')->onDelete('cascade');
            $table->unsignedBigInteger('inclusion_id');
            // $table->foreign('inclusion_id')->references('id')->on('equipments')->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamps();

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_inclusions');
    }
};
