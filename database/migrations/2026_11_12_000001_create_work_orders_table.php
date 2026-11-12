<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number', 50)->unique();
            $table->string('product_name', 255);
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('bom_id')->nullable()->index();
            $table->decimal('target_quantity', 12, 2);
            $table->decimal('actual_quantity', 12, 2)->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->decimal('actual_emission', 15, 4)->nullable(); // kg CO2e
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
