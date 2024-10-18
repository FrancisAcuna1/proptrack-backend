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
        Schema::create('rented_unit_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rental_agreement_id');
            $table->string('rented_room_number'); 
            $table->integer('rented_bed_number'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rented_unit_details');
    }
};
