<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variant_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_variant_id');
            $table->string('image');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('product_variant_id')
            ->references('id')
            ->on('product_variants')
            ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variant_images', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
        });
        Schema::dropIfExists('product_variant_images');
    }
};
