<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_variant_storage_id');
            $table->integer('quantity');
            $table->tinyInteger('status')
                ->default(0)
                ->comment('0 = active, 1 = converted_to_order');

            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->index(['product_id', 'product_variant_storage_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            $table->foreign('product_variant_storage_id')
                ->references('id')
                ->on('product_variant_storages')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_variant_storage_id']);
        });
        Schema::dropIfExists('carts');
    }
};
