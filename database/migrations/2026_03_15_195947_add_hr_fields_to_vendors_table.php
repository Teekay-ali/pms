<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('type');
            $table->string('payment_terms')->nullable()->after('hourly_rate');
            $table->date('contract_expiry')->nullable()->after('payment_terms');
            $table->date('insurance_expiry')->nullable()->after('contract_expiry');
            $table->unsignedTinyInteger('rating')->nullable()->after('insurance_expiry');
            $table->text('notes')->nullable()->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['hourly_rate', 'payment_terms', 'contract_expiry', 'insurance_expiry', 'rating', 'notes']);
        });
    }
};
