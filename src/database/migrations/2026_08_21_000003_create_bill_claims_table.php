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
        Schema::create('bill_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->onDelete('cascade');
            $table->string('payer_name');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_method')->default('qris');
            $table->timestamps();
        });

        Schema::create('bill_claim_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_claim_id')->constrained('bill_claims')->onDelete('cascade');
            $table->foreignId('bill_item_id')->constrained('bill_items')->onDelete('cascade');
            $table->integer('qty')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_claim_items');
        Schema::dropIfExists('bill_claims');
    }
};
