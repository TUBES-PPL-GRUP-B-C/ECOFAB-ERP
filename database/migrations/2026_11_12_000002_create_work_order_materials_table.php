<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('work_order_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->string('material_name', 255);
            $table->unsignedBigInteger('material_id')->nullable()->index();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('required_quantity', 12, 2);
            $table->decimal('actual_quantity', 12, 2)->nullable();
            $table->string('unit', 20)->default('kg');
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('emission_factor', 12, 4)->default(0); // kg CO2e per unit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_materials');
    }
};
