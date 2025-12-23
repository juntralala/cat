<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('transaction_id', 100);
            $table->string('sku_id', 100);
            $table->string('measurement_unit_id', 100);
            $table->integer('quantity');
            $table->decimal('price', 22, 4);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('sku_id')->references('id')->on('skus')->onDelete('cascade');
            $table->foreign('measurement_unit_id')->references('id')->on('measurement_units');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
