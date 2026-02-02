<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 50)->unique()->comment('Human-readable order number');
            $table->uuid('user_id');
            $table->unsignedInteger('shipping_method_id');
            $table->decimal('total_delivery_weight', 8, 2)->default(0.00)->comment('Total Shipping weight to the order');
            $table->decimal('total_delivery_charge', 10, 2)->default(0.00)->comment('Shipping cost applied to the order');
            $table->decimal('total_order_amount', 10, 2)->comment('Sum of all order items + delivery charge');
            $table->tinyInteger('order_status')->default(0)->comment('0=Pending, 1=Delivered, 2=Cancelled');
            $table->unsignedInteger('address_id')->comment('Required only for delivery_type=3 and active address');
            $table->timestamp('ordered_at')
                ->comment('When the order was placed');
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->foreign('shipping_method_id')
                ->references('id')
                ->on('shipping_methods')
                ->cascadeOnDelete();
            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->cascadeOnDelete();
            $table->index('order_status');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['shipping_method_id']);
            $table->dropForeign(['address_id']);
        });
        Schema::dropIfExists('orders');
    }
};
