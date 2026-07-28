<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opname_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('stock_opname_sessions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('counted_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assignment_id')->nullable()->constrained('opname_assignments')->nullOnDelete();
            $table->integer('system_stock')->default(0);
            $table->integer('physical_stock')->default(0);
            $table->integer('variance_qty')->default(0);
            $table->decimal('variance_value', 15, 2)->default(0);
            $table->string('unit')->default('PCS');
            $table->text('notes')->nullable();
            $table->integer('count_round')->default(1);
            $table->enum('count_method', ['SCAN', 'MANUAL'])->default('MANUAL');
            $table->timestamp('counted_at');
            $table->timestamps();

            $table->index(['session_id', 'product_id']);
            $table->index(['session_id', 'counted_by']);
            $table->index(['session_id', 'variance_qty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opname_counts');
    }
};
