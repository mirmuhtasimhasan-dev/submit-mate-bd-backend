<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->foreignId('service_id')
                  ->constrained('services')
                  ->onDelete('cascade');

            $table->foreignId('package_id')
                  ->nullable()
                  ->constrained('packages')
                  ->onDelete('set null');

            $table->string('order_number')->unique();

            $table->string('title');
            $table->text('instructions')->nullable();
            $table->date('deadline')->nullable();

            $table->decimal('total_amount', 10, 2)->default(0);

            $table->enum('status', [
                'pending',
                'accepted',
                'in_progress',
                'need_more_info',
                'completed',
                'delivered',
                'cancelled'
            ])->default('pending');

            $table->enum('payment_status', [
                'unpaid',
                'partial',
                'paid',
                'refunded'
            ])->default('unpaid');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};