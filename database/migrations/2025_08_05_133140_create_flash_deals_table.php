<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flash_deals', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('deal_type')->nullable(); // Shunda keyingi migratsiya xatolik bermaydi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_deals');
    }
};
