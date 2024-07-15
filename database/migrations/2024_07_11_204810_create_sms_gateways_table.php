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
        Schema::create('sms_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address');
            $table->string('port');
            $table->string('description')->nullable();
            $table->boolean('is_network_roaming')->nullable(); // Is roaming present (true) or not (false)
            $table->string('network_operator_name')->nullable(); // Network operator name
            $table->string('sim_state')->nullable(); // SIM state
            $table->string('display_name')->nullable(); // Carrier name set by user
            $table->string('carrier_name')->nullable(); // Carrier name
            $table->integer('sim_slot')->nullable(); // Sim slot number
            $table->string('telephony_sim_state')->nullable(); // SIM state for telephonies
            $table->string('telephony_network_operator_name')->nullable(); // Network operator name for telephonies
            $table->string('battery_status')->nullable(); // Power status
            $table->float('battery_level')->nullable(); // Battery level
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_gateways');
    }
};
