<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->date('work_date')->index();
            $table->string('shift', 50);
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('status', 30)->default('hadir')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
