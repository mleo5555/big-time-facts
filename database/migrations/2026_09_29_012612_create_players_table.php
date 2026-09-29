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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            // Set when a signed-in user joins, so their games show up in their history.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nickname', 20);
            $table->timestamps();

            // The real guard against duplicate nicknames: validation alone can't stop two
            // simultaneous joins from both passing the "is it taken?" check.
            $table->unique(['room_id', 'nickname']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
