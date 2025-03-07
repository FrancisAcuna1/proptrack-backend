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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->morphs('unit');
            $table->string('category');
            $table->string('type_of_bills')->nullable();
            $table->string('type_of_tax')->nullable();
            $table->decimal('amount');
            $table->date('expense_date');
            $table->string('frequency')->nullable();
            $table->boolean('recurring')->nullable();
            $table->string('status')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
