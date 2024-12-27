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
        if (!Schema::hasTable('apartment')) {
            Schema::create('apartment', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('property_id');  // Ensure this is unsignedBigInteger
                $table->string('apartment_name');
                $table->integer('number_of_rooms');
                $table->string('capacity');
                $table->decimal('rental_fee', 10, 2);
                // $table->string('rental_fee');
                // $table->string('payor_name');
                $table->enum('status', ['Available', 'Occupied']);
                $table->string('property_type');
                $table->string('building_no');
                $table->string('street');
                $table->string('barangay');
                $table->string('municipality');
                $table->timestamps();
            });
        }
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment');
    }
};
