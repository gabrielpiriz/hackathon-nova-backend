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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_sold');
            $table->decimal('unit_price_ars', 12, 2);
            $table->decimal('unit_price_usd', 12, 2);
            $table->decimal('total_amount_ars', 15, 2);
            $table->decimal('total_amount_usd', 15, 2);
            $table->datetime('sale_date');
            $table->string('buyer_name');
            $table->string('buyer_contact')->nullable();
            $table->enum('payment_method', ['cash', 'transfer', 'check', 'credit'])->default('transfer');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'sale_date']);
            $table->index('sale_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
