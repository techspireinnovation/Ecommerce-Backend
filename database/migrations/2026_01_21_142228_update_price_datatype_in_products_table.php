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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
            $table->decimal('discount_percentage', 5, 2)->nullable()->change();

            $table->decimal('weight', 8, 2)
                  ->after('policies')
                  ->comment('Product weight in kilograms (kg)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('price')->change();
            $table->integer('discount_percentage')->nullable()->change();

            $table->dropColumn('weight');
        });
    }
};
