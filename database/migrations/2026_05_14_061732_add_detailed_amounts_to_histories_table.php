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
            $table->decimal('subtotal', 15, 2)->default(0)->after('customer_id');
            $table->decimal('tax', 15, 2)->default(0)->after('subtotal');
            $table->decimal('service', 15, 2)->default(0)->after('tax');
            $table->decimal('promo_discount', 15, 2)->default(0)->after('service');
            $table->decimal('points_discount', 15, 2)->default(0)->after('promo_discount');
            $table->decimal('tier_discount', 15, 2)->default(0)->after('points_discount');
            $table->decimal('voucher_discount', 15, 2)->default(0)->after('tier_discount');
            $table->foreignId('voucher_id')->nullable()->after('voucher_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal', 'tax', 'service',
                'promo_discount', 'points_discount', 'tier_discount',
                'voucher_discount', 'voucher_id',
            ]);
        });
    }
};
