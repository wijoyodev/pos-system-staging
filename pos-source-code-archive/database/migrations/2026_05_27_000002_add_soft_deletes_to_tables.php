<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('branches', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('promotions', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('vouchers', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('categories', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('suppliers', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('customers', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('users', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('branches', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('stores', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('promotions', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('vouchers', fn (Blueprint $t) => $t->dropSoftDeletes());
    }
};
