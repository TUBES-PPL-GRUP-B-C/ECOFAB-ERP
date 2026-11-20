<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('waste_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_listing_id')->constrained('waste_listings')->restrictOnDelete();
            $table->string('buyer_name', 100);
            $table->string('buyer_contact', 100);
            $table->decimal('requested_weight', 12, 3);
            $table->decimal('price_per_kg', 15, 2);
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('decision_reason', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_orders');
    }
};
