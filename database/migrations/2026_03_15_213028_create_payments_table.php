<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            // Polymorphic — payment can belong to an invoice or a bill
            $table->morphs('payable');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('method', ['cash', 'bank_transfer', 'cheque', 'card', 'other'])->default('bank_transfer');
            $table->string('reference')->nullable();   // transaction ref, cheque no, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
