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
        if (!Schema::hasTable('property')) {
            Schema::create('property', function (Blueprint $table) {
                $table->id();
                $table->string('propertyname');
                $table->string('street');
                $table->string('barangay');
                $table->string('municipality');
                $table->string('image');
                $table->timestamps();
            });
        }    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property');
    }
};
