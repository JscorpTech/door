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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->mediumText('slug');
            $table->string('readable_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('writer')->nullable();
            $table->string('title');
            $table->longText('description');
            $table->string('image')->nullable();
            $table->string('image_storage_type',15)->default('public')->nullable();
            $table->string('draft_image')->nullable();
            $table->string('draft_image_storage_type',15)->default('public')->nullable();
            $table->datetime('publish_date')->default(now());
            $table->tinyInteger('is_published')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('is_draft')->default(0);
            $table->text('draft_data')->nullable();
            $table->integer('click_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
