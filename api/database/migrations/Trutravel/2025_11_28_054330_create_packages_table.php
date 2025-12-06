<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('trutravel')->create('packages', function (Blueprint $table) {
            $table->uuid('_id')->primary();
            $table->string('package_type');
            $table->string('name');
            $table->text('description');
            $table->string('skyroute_origin_id');
            $table->string('skyroute_destination_id');
            $table->string('skyroute_vehicle_id')->nullable();
            $table->string('airline_flight_id');
            $table->string('airline_return_flight_id')->nullable();
            $table->string('aureliya_property_id');
            $table->integer('nights')->default(2);
            $table->decimal('base_price', 10, 2);
            $table->decimal('discount_rate', 5, 2);
            $table->decimal('final_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('trutravel')->dropIfExists('packages');
    }
};
