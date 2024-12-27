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
        if(!Schema::hasTable('schedule_maintenances')){
            Schema::create('schedule_maintenances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('maintenance_request_id')->nullable();
                $table->nullableMorphs('unit'); 
                $table->string('maintenance_task')->nullable();
                $table->string('schedule_title');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status');
                $table->string('text_color');
                $table->string('bg_color');
                $table->text('description')->nullable();
                $table->boolean('is_reported_issue')->nullable();
                $table->timestamps();
            });
        }
        
    }   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_maintenances');
    }
};
