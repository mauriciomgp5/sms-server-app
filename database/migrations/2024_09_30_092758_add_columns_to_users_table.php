<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('email');
            $table->boolean('is_admin')->default(false)->after('is_active');
            $table->boolean('is_notifiable')->default(false)->after('is_admin');
            $table->string('phone_number')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('is_admin');
            $table->dropColumn('is_notifiable');
            $table->dropColumn('phone_number');
        });
    }
};
