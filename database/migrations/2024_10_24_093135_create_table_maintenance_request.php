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
        Schema::create('maintenance_request', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('reported_issue')->nullable();
            $table->string('other_issue')->nullable();
            // $table->string('unit_type');
            $table->text('issue_description');
            $table->date('date_reported');
            $table->string('status');
            $table->date('urgency_level')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_schedule')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_request');
    }
};
