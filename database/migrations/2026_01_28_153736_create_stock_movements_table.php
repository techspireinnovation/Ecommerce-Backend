<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_variant_storage_id');
            $table->unsignedBigInteger('order_id');

            $table->tinyInteger('type')->comment('1=order, 2=cancel');
            $table->integer('quantity')->comment('positive integer only');
            $table->timestamp('created_at')->nullable();

            $table->index('product_variant_storage_id');
            $table->index('order_id');

            $table->foreign('product_variant_storage_id')
                ->references('id')
                ->on('product_variant_storages')
                ->cascadeOnDelete();
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['product_variant_storage_id']);
            $table->dropForeign(['order_id']);

        });
        Schema::dropIfExists('stock_movements');
    }
};
