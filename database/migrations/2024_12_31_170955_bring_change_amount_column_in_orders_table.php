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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('bring_change_amount', 18, 12)->after('paid_amount')->default(0)->nullable();
            $table->string('bring_change_amount_currency')->after('bring_change_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('bring_change_amount');
            $table->dropColumn('bring_change_amount_currency');
        });
    }
};
