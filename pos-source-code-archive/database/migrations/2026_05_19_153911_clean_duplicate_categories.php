<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('categories')
            ->select('name', DB::raw('MIN(id) as keep_id'))
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $deleteIds = DB::table('categories')
                ->where('name', $dup->name)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            DB::table('products')
                ->whereIn('category_id', $deleteIds)
                ->update(['category_id' => $dup->keep_id]);

            DB::table('categories')
                ->whereIn('id', $deleteIds)
                ->delete();
        }
    }

    public function down(): void {}
};
