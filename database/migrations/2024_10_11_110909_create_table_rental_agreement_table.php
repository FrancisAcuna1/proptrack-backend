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
        Schema::create('rental_agreements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('rented_unit_id');  // The ID of the apartment or boarding house
            $table->string('rented_unit_type');            // The type of rented unit (Apartment or BoardingHouse)
            $table->decimal('rental_fee', 10, 2);
            $table->decimal('initial_payment', 10, 2);
            $table->decimal('advance_payment', 10, 2)->nullable();
            $table->integer('prepaid_rent_period');  
            $table->decimal('deposit', 10, 2);  
            $table->date('lease_start_date');
            $table->boolean('is_last_month')->nullable();
            // $table->date('lease_end_date');      
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_agreements');
    }
};
