<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->onDelete('cascade');

            $table->string('method'); 
            // example: bkash, nagad, card, bank

            $table->string('provider')->nullable(); 
            // example: manual, sslcommerz, aamarpay, bkash_gateway

            $table->decimal('amount', 10, 2)->default(0);

            $table->string('sender_number')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('screenshot_path')->nullable();

            $table->string('gateway_payment_id')->nullable();

            $table->enum('status', [
                'pending',
                'verified',
                'rejected',
                'failed',
                'refunded'
            ])->default('pending');

            $table->timestamp('verified_at')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};