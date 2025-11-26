<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\aureliya\Property;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('aureliya')->create('reviews', function (Blueprint $table) {
            $table->uuid('_id')->primary();

            $table->uuid('property_id');
            $table->foreign('property_id')->references('_id')->on('properties');

            $table->string('user_id'); // Aeronexa user

            $table->tinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('aureliya')->dropIfExists('reviews');
    }
};
