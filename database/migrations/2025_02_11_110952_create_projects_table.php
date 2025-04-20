<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('images')->nullable();
            $table->string('projectType');
            $table->decimal('price', 15, 2);
            $table->text('description')->nullable();
            $table->json('videos')->nullable();
            $table->json('properties')->nullable();
            $table->string('address')->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 30, 25)->nullable();
            $table->string('region')->nullable();
            $table->string('developerInformation')->nullable();
            $table->json('neighborhood')->nullable();
            $table->json('communityFeatures')->nullable();
            $table->json('sustainabilityFeatures')->nullable();
            $table->json('investmentReason')->nullable();
            $table->json('amenities')->nullable();
            $table->integer('progress')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('investmentPotential')->nullable();
            $table->json('FAQ')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
