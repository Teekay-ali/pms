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
        Schema::create('discussion_reactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('reactable');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('like');
            $table->unique(['reactable_type', 'reactable_id', 'user_id', 'type'], 'discussion_reactions_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_reactions');
    }
};
