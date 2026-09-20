<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 10, 2);               
            $table->enum('type', ['income', 'expense']);
            $table->string('category')->default('Other');
            $table->string('currency', 3)->default('EUR'); 
            $table->decimal('original_amount', 10, 2)->nullable(); 
            $table->string('original_currency', 3)->nullable();    
            $table->string('payment_type')->default('manual');
            $table->integer('billing_day')->nullable();
            $table->date('imported_transaction_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
