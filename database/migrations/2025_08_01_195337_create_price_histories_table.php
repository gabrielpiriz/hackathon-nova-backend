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
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('price_ars', 12, 2);
            $table->decimal('price_usd', 12, 2);
            $table->enum('market_trend', ['up', 'down', 'stable'])->default('stable');
            $table->string('source')->default('system');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'date']);
            $table->index('date');
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
