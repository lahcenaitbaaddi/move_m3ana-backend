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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('promotable'); // promotable_id + promotable_type
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->boolean('is_boosted')->default(false);

            // ── Préparation future intégration paiement (Stripe) ──
            $table->string('payment_id')->nullable();
            $table->string('payment_status')->nullable();

            $table->timestamps();

            // ── Index pour les requêtes fréquentes ──
            $table->index(['status', 'end_date']);
            $table->index(['promotable_type', 'promotable_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
