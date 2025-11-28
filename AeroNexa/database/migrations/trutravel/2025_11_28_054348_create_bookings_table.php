<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // explicitly use mysql connection
        Schema::connection('trutravel')->create('bookings', function (Blueprint $table) {
            $table->bigIncrements('_id')->primary();

            // user id from AeroNexa (string to support different formats)
            $table->string('user_id');

            // FK to packages table (TruTravel package)
            $table->unsignedBigInteger('package_id')->nullable();

            // partner booking ids (strings from each microservice)
            $table->string('psa_booking_id')->nullable();
            $table->string('skyroute_booking_id')->nullable();
            $table->string('aureliya_booking_id')->nullable();

            // single AeroPay transaction code for the package
            $table->string('transaction_code')->nullable();

            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 8)->default('PHP');

            // payment and booking statuses
            $table->string('payment_status', 32)->default('pending'); // pending|paid|failed|cancelled
            $table->string('status', 32)->default('pending'); // pending|confirmed|cancelled

            // optional metadata (breakdown, other data)
            $table->json('metadata')->nullable();

            $table->timestamps();

            // indexes
            $table->index('user_id');
            $table->index('package_id');
            $table->index('transaction_code');

            // foreign key (optional cascade on delete)
            $table->foreignId('package_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->table('bookings', function (Blueprint $table) {
            // drop foreign before dropping table (safe)
            if (Schema::connection('mysql')->hasTable('bookings')) {
                $table->dropForeign(['package_id']);
            }
        });

        Schema::connection('mysql')->dropIfExists('bookings');
    }
}
