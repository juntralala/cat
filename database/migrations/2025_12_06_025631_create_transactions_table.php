<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->enum('type', ['in', 'out']); // 'in' untuk masuk, 'out' untuk keluar
            $table->string('recipient_id', 100)->nullable(); // untuk barang keluar
            $table->string('supplier')->nullable(); // untuk barang masuk
            $table->string('division')->nullable(); // untuk barang masuk
            $table->text('notes')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('recipient_id')->references('id')->on('recipients')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};