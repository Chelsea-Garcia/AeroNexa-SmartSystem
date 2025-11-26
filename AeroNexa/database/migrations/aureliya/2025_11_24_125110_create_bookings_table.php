<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('aureliya')->create('bookings', function (Blueprint $table) {
            $table->uuid('_id')->primary();

            $table->uuid('property_id');
            $table->foreign('property_id')->references('_id')->on('properties');

            $table->string('user_id'); // AEX User ID

            $table->dateTime('check_in');
            $table->dateTime('check_out');

            $table->double('total_price');

            $table->string('transaction_code')->nullable();
            $table->string('payment_method');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('aureliya')->dropIfExists('bookings');
    }
};
