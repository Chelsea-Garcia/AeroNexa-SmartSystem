<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('aeropay')->create('transactions', function (Blueprint $table) {

            // UUID-based primary key (string)
            $table->uuid('_id')->primary();

            // Human-readable transaction code (indexed & unique)
            $table->string('transaction_code', 20)->unique();

            // User from external system (Aeronexa)
            $table->string('user_id')->index();

            // The partner system using Aeropay (Aureliya, Aeronexa, etc.)
            $table->string('partner')->index();

            // Reference ID in the partner system (booking_id, order_id, etc.)
            $table->string('partner_reference_id')->index();

            // Monetary details
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('PHP');

            // Enum-like constraint (optional)
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])
                ->default('pending');

            // Flexible metadata JSON
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('aeropay')->dropIfExists('transactions');
    }
};
