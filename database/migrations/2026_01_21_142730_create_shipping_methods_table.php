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
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->increments('id');

            $table->tinyInteger('delivery_type')
                  ->comment('1 = Inside Valley, 2 = Outside Valley');

            $table->decimal('weight_from', 8, 2)
                  ->comment('Minimum applicable order weight in kilograms (kg)');

            $table->decimal('weight_to', 8, 2)
                  ->comment('Maximum applicable order weight in kilograms (kg)');

            $table->decimal('charge', 10, 2)
                  ->comment('shipping cost');

            $table->decimal('free_shipping_threshold', 10, 2)
                  ->default(0)
                  ->comment('Order subtotal required for free shipping (0 = disabled)');

            $table->tinyInteger('status')
                  ->default(0)
                  ->comment('0 = Active, 1 = Inactive');

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
