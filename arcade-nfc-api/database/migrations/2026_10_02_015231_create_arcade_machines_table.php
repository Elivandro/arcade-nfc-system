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
        Schema::create('arcade_machines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('api_token', 64)->unique();
            $table->boolean('is_online')->default(false);
            $table->decimal('cost_per_play', 8, 2)->default(1.00);
            $table->timestamp('last_maintenance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arcade_machines');
    }
};
