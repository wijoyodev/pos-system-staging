<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_name_unique');
        });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX categories_name_unique ON categories(name) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX categories_name_unique');
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('name');
        });
    }
};
