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
        Schema::create('stock_clearance_products', function (Blueprint $table) {
            $table->id();
            $table->string('added_by');
            $table->integer('product_id')->nullable();
            $table->integer('setup_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('shop_id')->nullable();
            $table->boolean('is_active')->default(0);
            $table->string('discount_type')->default('percentage')->nullable();
            $table->decimal('discount_amount', 18, 12)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_clearance_products');
    }
};
