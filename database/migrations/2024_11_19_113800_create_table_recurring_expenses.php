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
    {   if (!Schema::hasTable('recurring_expenses')) {
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->morphs('unit');
            $table->decimal('amount', 10, 2);
            $table->string('category');
            $table->string('type_of_bill')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->date('startDate');
            $table->date('endDate');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'paused', 'cancelled'])->default('active');
            $table->date('last_generated_date')->nullable();
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_expenses');
    }
};
