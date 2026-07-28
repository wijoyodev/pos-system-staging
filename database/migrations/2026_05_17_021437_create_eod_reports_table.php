<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eod_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained();
            $table->date('eod_date');
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('total_service', 15, 2)->default(0);
            $table->decimal('total_promo_discount', 15, 2)->default(0);
            $table->decimal('total_points_discount', 15, 2)->default(0);
            $table->decimal('total_tier_discount', 15, 2)->default(0);
            $table->decimal('total_voucher_discount', 15, 2)->default(0);
            $table->decimal('total_net_sales', 15, 2)->default(0);
            $table->decimal('sales_cash', 15, 2)->default(0);
            $table->decimal('sales_qris', 15, 2)->default(0);
            $table->decimal('sales_debit', 15, 2)->default(0);
            $table->decimal('sales_credit', 15, 2)->default(0);
            $table->decimal('total_expenses', 15, 2)->default(0);
            $table->decimal('total_expected_cash', 15, 2)->default(0);
            $table->decimal('total_actual_cash', 15, 2)->default(0);
            $table->decimal('cash_difference', 15, 2)->default(0);
            $table->integer('total_closings')->default(0);
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->foreignId('generated_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'eod_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eod_reports');
    }
};
