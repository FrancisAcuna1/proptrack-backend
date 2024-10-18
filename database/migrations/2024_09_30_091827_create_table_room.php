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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('boardinghouse_id');  // Ensure this is unsignedBigInteger
            // $table->foreignId('boardinghouse_id')->constrained('boarding_house')->onDelete('cascade');
            $table->integer('room_number');
            $table->integer('number_of_beds')->nullable();  
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
