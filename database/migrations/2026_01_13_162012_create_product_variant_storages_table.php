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
        Schema::create('product_variant_storages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_variant_id');
            $table->string('storage', 100); // 64GB, 128GB
            $table->string('sku', 50)->unique();
            $table->integer('quantity');
            $table->integer('low_stock_threshold');
            $table->timestamps();
            $table->softDeletes();

            $table->index('product_variant_id');
            $table->foreign('product_variant_id')
                  ->references('id')->on('product_variants')
                  ->cascadeOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variant_storages', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropIndex(['product_variant_id']);
        });
        Schema::dropIfExists('product_variant_storages');
    }
};
