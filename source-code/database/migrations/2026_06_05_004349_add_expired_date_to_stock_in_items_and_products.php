<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->date('expired_date')->nullable()->after('cost_price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->date('expired_date')->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropColumn('expired_date');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('expired_date');
        });
    }
};
