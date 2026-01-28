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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
            $table->decimal('discount_percentage', 5, 2)->nullable()->change();
            $table->tinyInteger('weight_type')
                ->after('policies')
                ->comment('1=gram, 2=kilogram');

            $table->decimal('weight', 8, 2)
                ->after('weight_type')
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
            $table->dropColumn('weight_type');
            $table->dropColumn('weight');
        });
    }
};
