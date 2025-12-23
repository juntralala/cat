<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_units', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('name', 255)->unique();
            $table->string('base_measurement_unit_id', 100)->nullable();
            $table->integer('conversion')->nullable();
            $table->boolean('is_base')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('base_measurement_unit_id')->on('measurement_units')->references('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_units');
    }
};
