<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // explicitly use mysql connection
        Schema::connection('trutravel')->create('packages', function (Blueprint $table) {
            $table->bigIncrements('_id')->primary();

            $table->string('name');
            $table->text('description')->nullable();

            // store partner ids as strings (they come from other microservices)
            $table->string('skyroute_origin_id')->nullable();
            $table->string('skyroute_destination_id')->nullable();
            $table->string('skyroute_vehicle_id')->nullable();
            $table->string('airline_flight_id')->nullable();
            $table->string('aureliya_property_id')->nullable();

            // prices: decimal(10,2)
            $table->decimal('base_price', 10, 2)->default(0);
            // discount_rate stored as fraction (e.g. 0.15 for 15%). Use decimal to avoid float issues.
            $table->decimal('discount_rate', 5, 2)->nullable()->default(0);
            $table->decimal('final_price', 10, 2)->default(0);

            $table->timestamps();

            // indexes to speed lookups
            $table->index('skyroute_origin_id');
            $table->index('skyroute_destination_id');
            $table->index('airline_flight_id');
            $table->index('aureliya_property_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->dropIfExists('packages');
    }
}
