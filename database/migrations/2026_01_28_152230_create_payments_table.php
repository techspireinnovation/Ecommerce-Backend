<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');

            // Self reference for refunds
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete()
                ->comment('Original payment id for refunds');

            $table->tinyInteger('method')
                ->comment('1=COD, 2=Online, 3=Wallet');

            $table->tinyInteger('gateway')
                ->nullable()
                ->comment('1=Khalti, 2=eSewa, 3=Stripe');

            $table->decimal('amount', 10, 2)
                ->comment('Transaction amount');

            $table->tinyInteger('status')
                ->default(0)
                ->comment('0=pending, 1=success, 2=failed');

            $table->string('transaction_id', 100)
                ->nullable()
                ->unique()
                ->comment('Gateway transaction id');

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index('order_id');
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
        Schema::dropIfExists('payments');
    }
};
