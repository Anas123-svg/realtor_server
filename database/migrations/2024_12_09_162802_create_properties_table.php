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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->unsignedInteger('bedrooms')->nullable();
            $table->unsignedInteger('bathrooms')->nullable();
            $table->unsignedInteger('area')->nullable();
            $table->string('propertyType')->nullable();
            $table->string('category')->nullable();
            $table->string('dealType')->nullable();
            $table->string('leaseTerm')->nullable();
            $table->unsignedInteger('floors')->nullable();
            $table->string('noiseLevel')->nullable();
            $table->string('laundry')->nullable();
            $table->string('internet')->nullable();
            $table->string('condition')->nullable();
            $table->string('video')->nullable();
            $table->Decimal('price', 10, 2);
            $table->json('view')->nullable();
            $table->json('outdoor')->nullable();
            $table->json('propertyStyle')->nullable();
            $table->json('securityFeatures')->nullable();
            $table->json('amenities')->nullable();
            $table->json('heating')->nullable();
            $table->json('cooling')->nullable();
            $table->string('priceType');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('likes')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
