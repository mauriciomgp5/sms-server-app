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
        Schema::create('sms_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gateway_id')->constrained('sms_gateways')->onDelete('cascade');
            $table->integer('slot_number');
            $table->boolean('is_active')->default(true);
            $table->integer('sent_count')->default(0);
            $table->integer('max_sends')->default(800);
            $table->string('phone_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_slots');
    }
};
