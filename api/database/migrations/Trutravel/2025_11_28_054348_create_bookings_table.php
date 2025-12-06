<?php

// ============================================
// MIGRATION FILE
// database/migrations/xxxx_xx_xx_create_bookings_table.php
// ============================================

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        Schema::connection('trutravel')->create('bookings', function (Blueprint $table) {
            $table->uuid('_id')->primary();
            $table->string('user_id');
            $table->uuid('package_id');
            $table->date('travel_date');
            $table->date('return_date');
            $table->string('transaction_code')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 8)->default('PHP');
            $table->string('payment_status', 32)->default('pending');
            $table->string('status', 32)->default('pending');
            $table->json('payment_breakdown')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('package_id');
            $table->index('transaction_code');
        });
    }

    public function down()
    {
        Schema::connection('trutravel')->dropIfExists('bookings');
    }
}
