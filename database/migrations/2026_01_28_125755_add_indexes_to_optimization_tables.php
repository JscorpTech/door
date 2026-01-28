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
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('sub_category_id');
            $table->index('sub_sub_category_id');
            $table->index('brand_id');
            $table->index('status');
            $table->index('featured');
            $table->index('added_by');
            $table->index('user_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('parent_id');
            $table->index('position');
            $table->index('priority');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('order_id');
            $table->index('delivery_status');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['sub_category_id']);
            $table->dropIndex(['sub_sub_category_id']);
            $table->dropIndex(['brand_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['featured']);
            $table->dropIndex(['added_by']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['position']);
            $table->dropIndex(['priority']);
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['order_id']);
            $table->dropIndex(['delivery_status']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['status']);
        });
    }
};
