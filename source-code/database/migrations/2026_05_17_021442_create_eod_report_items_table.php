<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eod_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eod_report_id')->constrained('eod_reports')->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->integer('total_qty_sold');
            $table->decimal('total_revenue', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eod_report_items');
    }
};
