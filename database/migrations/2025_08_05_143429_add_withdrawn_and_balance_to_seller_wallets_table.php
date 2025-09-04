<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('seller_wallets', function (Blueprint $table) {
            if (!Schema::hasColumn('seller_wallets', 'withdrawn')) {
                $table->string('withdrawn')->nullable();
            }
            if (!Schema::hasColumn('seller_wallets', 'balance')) {
                $table->string('balance')->nullable();
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_wallets', function (Blueprint $table) {
            //
        });
    }
};
