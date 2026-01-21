<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_variant_storage_id');

            $table->tinyInteger('status')
                  ->default(0)
                  ->comment('0 = active, 1 = moved_to_cart');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'product_variant_storage_id']);

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->cascadeOnDelete();

            $table->foreign('product_variant_storage_id')
                  ->references('id')
                  ->on('product_variant_storages')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
         Schema::table('wishlists', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_variant_storage_id']);
         });
        Schema::dropIfExists('wishlists');
    }
};
