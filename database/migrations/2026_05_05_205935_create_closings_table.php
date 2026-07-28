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
        Schema::create('closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('shift'); // morning, afternoon, night
            $table->decimal('opening_balance', 15, 0)->default(0);
            $table->decimal('total_sales', 15, 0)->default(0);
            $table->decimal('cash_sales', 15, 0)->default(0);
            $table->decimal('qris_sales', 15, 0)->default(0);
            $table->decimal('debit_sales', 15, 0)->default(0);
            $table->decimal('credit_sales', 15, 0)->default(0);
            $table->decimal('expenses', 15, 0)->default(0);
            $table->decimal('expected_cash', 15, 0)->default(0);
            $table->decimal('actual_cash', 15, 0)->default(0);
            $table->decimal('difference', 15, 0)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->date('closing_date')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closings');
    }
};
