<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_options', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->string('carrier', 50);
            $table->string('service', 50);
            $table->unsignedInteger('price_cents');
            $table->unsignedSmallInteger('delivery_days');
            $table->timestamps();

            $table->index('quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_options');
    }
};
