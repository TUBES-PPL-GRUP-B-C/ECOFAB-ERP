<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('waste_listings', function (Blueprint $table) {
            $table->id();
            // FR-22 owns waste_records; add the foreign key after its schema is available.
            $table->foreignId('waste_record_id')->index();
            $table->string('photo_path')->nullable();
            $table->string('category');
            $table->decimal('price_per_kg', 15, 2);
            $table->decimal('available_weight', 12, 3);
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_listings');
    }
};
