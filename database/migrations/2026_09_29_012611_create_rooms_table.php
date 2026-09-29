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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            // Nullable so a finished room can release its code for reuse. Unique indexes allow
            // multiple NULLs, and the index also serves join-by-code lookups.
            $table->string('code', 4)->nullable()->unique();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('lobby');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
