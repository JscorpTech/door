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
        // admin_confirmation_status
        Schema::table("orders", function (Blueprint $table) {
            $table->string("admin_confirmation_status", 20)->default("pending")->after("order_status");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("orders", function (Blueprint $table) {
            $table->dropColumn("admin_confirmation_status");
        });
    }
};
