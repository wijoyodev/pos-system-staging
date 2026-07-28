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
        Schema::table('histories', function (Blueprint $table) {
            $table->string('void_otp')->nullable()->after('status');
            $table->timestamp('void_otp_expires_at')->nullable()->after('void_otp');
            $table->foreignId('void_otp_admin_id')->nullable()->after('void_otp_expires_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            $table->dropForeign(['void_otp_admin_id']);
            $table->dropColumn(['void_otp', 'void_otp_expires_at', 'void_otp_admin_id']);
        });
    }
};
