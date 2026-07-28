<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->enum('type', [
                'percentage',           // Diskon %
                'nominal',            // Diskon fixed amount
                'buy_x_get_y',       // BOGO
                'bundle',            // Bundle pricing
                'min_purchase',      // Min purchase discount
                'member',            // Member only discount
                'time_based',        // Waktu tertentu
                'category',          // Per kategori
                'product',           // Per produk spesifik
                'tiered',           // Tiered discount
                'voucher',          // Kode kupon
            ]);
            $table->string('value')->nullable();  // JSON untuk tiered/bundle etc
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_nominal', 15, 2)->nullable();
            $table->decimal('min_purchase_amount', 15, 2)->nullable();
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            $table->integer('min_quantity')->nullable();
            $table->integer('max_quantity')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('buy_product_id')->nullable()->constrained('products', 'id')->nullOnDelete();
            $table->foreignId('get_product_id')->nullable()->constrained('products', 'id')->nullOnDelete();
            $table->integer('buy_quantity')->nullable();
            $table->integer('get_quantity')->nullable();
            $table->json('tiers')->nullable();  // JSON untuk tiered discount
            $table->json('products')->nullable();  // JSON array untuk bundle
            $table->decimal('bundle_price', 15, 2)->nullable();
            $table->json('eligibleRoles')->nullable();  // JSON array untuk member roles
            $table->string('day_of_week')->nullable();  // comma separated: 0,1,2,3,4,5,6
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('stackable')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
