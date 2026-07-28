<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('history_items', function (Blueprint $table) {
            $table->integer('discount')->default(0)->after('price');
            $table->string('discount_description')->nullable()->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('history_items', function (Blueprint $table) {
            $table->dropColumn(['discount', 'discount_description']);
        });
    }
};
