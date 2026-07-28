<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payments');

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('history_id')->constrained()->cascadeOnDelete();
            $table->string('method');
            $table->bigInteger('amount');
            $table->string('card_number', 25)->nullable();
            $table->string('cardholder_name')->nullable();
            $table->string('approval_code', 50)->nullable();
            $table->string('bank_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
