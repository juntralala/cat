<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skus', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('item_id', 100);
            $table->string('sku', 100);
            $table->string('spesification_name', 255);
            $table->integer('quantity');
            $table->decimal('price', 22, 4);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skus');
    }
};