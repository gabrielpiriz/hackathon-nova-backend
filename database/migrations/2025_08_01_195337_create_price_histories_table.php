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
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_type_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('average_price_ars', 12, 2);
            $table->decimal('average_price_usd', 12, 2);
            $table->enum('market_trend', ['up', 'down', 'stable'])->default('stable');
            $table->string('source')->default('manual');
            $table->decimal('weight_range_min', 8, 2)->nullable();
            $table->decimal('weight_range_max', 8, 2)->nullable();
            $table->integer('age_range_min')->nullable();
            $table->integer('age_range_max')->nullable();
            $table->timestamps();

            $table->index(['animal_type_id', 'date']);
            $table->index('date');
            $table->unique(['animal_type_id', 'date', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
