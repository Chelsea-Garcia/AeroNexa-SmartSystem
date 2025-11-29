<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('aureliya')->create('properties', function (Blueprint $table) {
            $table->uuid('_id')->primary();

            $table->string('title');
            $table->text('description');

            $table->string('country');
            $table->string('division');   // ← Added (region / prefecture / state)
            $table->string('city');

            $table->string('address');    // ← Full address

            $table->enum('type', [
                'apartment',
                'house',
                'hotel',
                'resort',
                'room',
                'villa',
                'guesthouse'
            ]);

            $table->double('price_per_night');
            $table->integer('max_guests');

            $table->json('photos')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('aureliya')->dropIfExists('properties');
    }
};
