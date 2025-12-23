<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sku_measurement_unit_conversions', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('measurement_unit_id', 100);
            $table->string('sku_id', 100);
            $table->integer('conversion');
            $table->timestamps();

            $table->unique(['measurement_unit_id', 'sku_id'], "uk_conversion_unit_sku");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sku_measurement_unit_conversions');
    }
};
