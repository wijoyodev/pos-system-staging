<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opname_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('stock_opname_sessions')->onDelete('cascade');
            $table->foreignId('count_id')->constrained('opname_counts')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('adjustment_qty')->default(0);
            $table->decimal('adjustment_value', 15, 2)->default(0);
            $table->enum('type', ['ADD', 'REDUCE', 'NO_ADJUSTMENT'])->default('NO_ADJUSTMENT');
            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'APPLIED'])->default('PENDING');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'status']);
            $table->index(['count_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opname_adjustments');
    }
};
