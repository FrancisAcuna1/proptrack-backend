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
        if (!Schema::hasTable('boarding_houses')) {
            Schema::create('boarding_houses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('property_id');  // Ensure this is unsignedBigInteger
                // $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
                $table->string('boarding_house_name');
                $table->integer('number_of_rooms');
                $table->string('capacity');
                // $table->string('rental_fee');
                // $table->string('payor_name');
                $table->enum('status', ['Available', 'Occupied']);
                $table->string('property_type');
                // $table->string('inclusion');
                $table->string('building_no');
                $table->string('street');
                $table->string('barangay');
                $table->string('municipality');
                // $table->string('image');
                $table->timestamps();
            });
        }    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boarding_houses');
    }
};
