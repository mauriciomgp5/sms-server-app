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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('primary_document')->nullable();
            $table->string('secondary_document')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->json('phones')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('address')->nullable();
            $table->string('address_number')->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
