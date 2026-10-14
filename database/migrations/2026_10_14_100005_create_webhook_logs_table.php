<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUuid('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $table->json('payload');
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->boolean('success')->default(false);
            $table->timestamps();

            $table->index('store_id');
            $table->index('shipment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
