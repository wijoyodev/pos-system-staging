<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            if (! Schema::hasColumn('histories', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
            }
            if (! Schema::hasColumn('histories', 'points_earned')) {
                $table->integer('points_earned')->default(0)->after('change_amount');
            }
            if (! Schema::hasColumn('histories', 'points_used')) {
                $table->integer('points_used')->default(0)->after('points_earned');
            }
            if (! Schema::hasColumn('histories', 'points_redeemed')) {
                $table->integer('points_redeemed')->default(0)->after('points_used');
            }
        });
    }

    public function down(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            if (Schema::hasColumn('histories', 'customer_id')) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn(['customer_id']);
            }
            if (Schema::hasColumn('histories', 'points_redeemed')) {
                $table->dropColumn('points_redeemed');
            }
            if (Schema::hasColumn('histories', 'points_used')) {
                $table->dropColumn('points_used');
            }
            if (Schema::hasColumn('histories', 'points_earned')) {
                $table->dropColumn('points_earned');
            }
        });
    }
};
