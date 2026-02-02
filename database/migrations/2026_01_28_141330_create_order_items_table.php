<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_variant_storage_id');
            $table->unsignedBigInteger('order_id');

            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2)->comment('quantity * unit_price - discount_amount');
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('product_variant_storage_id')
                ->references('id')
                ->on('product_variant_storages')
                ->cascadeOnDelete();
                $table->foreign('order_id')
                    ->references('id')
                    ->on('orders')
                    ->cascadeOnDelete();
            $table->index(['product_id', 'product_variant_storage_id', 'order_id']);
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_variant_storage_id']);
            $table->dropForeign(['order_id']);

        });
        Schema::dropIfExists('order_items');
    }
};
