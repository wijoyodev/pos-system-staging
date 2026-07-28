<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', [
                'PLANNED',
                'CETAK_KERTAS',
                'ENTRY',
                'CHECK_DATA',
                'PROSES',
                'CETAK_SELISIH',
                'EDIT_DATA',
                'FIXED',
                'ADJUST',
                'POSTED',
                'CANCELLED',
            ])->default('PLANNED');
            $table->date('planned_date');
            $table->date('cetakkertas_date')->nullable();
            $table->date('entry_date')->nullable();
            $table->date('checkdata_date')->nullable();
            $table->date('proses_date')->nullable();
            $table->date('cetakselisih_date')->nullable();
            $table->date('editdata_date')->nullable();
            $table->date('fixed_date')->nullable();
            $table->date('adjust_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('adjusted_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
            $table->index('planned_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_sessions');
    }
};
