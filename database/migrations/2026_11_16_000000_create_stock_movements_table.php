<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     * FR-11: Mutasi Stok & Jejak Audit (stock_movements)
     * Sesuai NFR-04 (integritas stok anti-minus) & NFR-12 (jejak audit pergerakan)
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->dateTime('movement_date')->useCurrent();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->unsignedBigInteger('product_id')->nullable()->comment('Disediakan untuk pergerakan produk jadi (FR-14)');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('type', 30)->comment('in, out, transfer, adjustment');
            $table->decimal('qty', 12, 2);
            $table->string('reference_type', 50)->nullable()->comment('GRN, WO, DO, TRANSFER, ADJUSTMENT');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['movement_date', 'type']);
            $table->index(['material_id', 'movement_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
