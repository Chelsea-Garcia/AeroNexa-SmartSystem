<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('aureliya')->create('property_amenities', function (Blueprint $table) {
            $table->uuid('property_id');
            $table->uuid('amenity_id');

            $table->foreign('property_id')->references('_id')->on('properties')->onDelete('cascade');
            $table->foreign('amenity_id')->references('_id')->on('amenities')->onDelete('cascade');

            $table->primary(['property_id', 'amenity_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('aureliya')->dropIfExists('property_amenity');
    }
};
