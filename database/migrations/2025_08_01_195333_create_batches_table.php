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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('animal_type_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('age_months');
            $table->decimal('average_weight_kg', 8, 2);
            $table->decimal('suggested_price_ars', 12, 2);
            $table->decimal('suggested_price_usd', 12, 2);
            $table->enum('status', ['available', 'sold', 'reserved'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['producer_id', 'status']);
            $table->index(['animal_type_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
