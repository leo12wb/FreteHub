<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('origin_zipcode', 8);
            $table->string('destination_zipcode', 8);
            $table->decimal('weight_kg', 6, 3);
            $table->json('dimensions');
            $table->unsignedInteger('declared_value_cents');
            $table->enum('status', ['pending', 'confirmed', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('store_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
