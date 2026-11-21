<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     * FR-10: Kelola Material Sirkular & Bahan Baku (materials)
     */
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 30)->unique();
            $table->string('name', 150);
            $table->string('category', 50)->comment('kayu, rotan, perekat, logam, bambu, finishing');
            $table->string('unit', 20)->comment('kg, liter, meter, pcs');
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->decimal('unit_cost', 14, 2)->default(0)->comment('Biaya standar satuan (Rp)');
            $table->foreignId('emission_factor_id')->nullable()->constrained('emission_factors')->nullOnDelete();
            $table->timestamps();

            $table->index(['sku', 'category']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
