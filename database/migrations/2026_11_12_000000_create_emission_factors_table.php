<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('emission_factors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('type', 20);
            $table->string('unit', 20);
            $table->decimal('value_kg_co2e', 15, 5);
            $table->string('source', 200)->nullable();
            $table->smallInteger('year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emission_factors');
    }
};
