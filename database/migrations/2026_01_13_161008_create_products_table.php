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
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 100)->unique();
            $table->string('name', 100);
            $table->unsignedInteger('brand_id');
            $table->unsignedInteger('subcategory_id');
            $table->text('summary');
            $table->longText('overview');
            $table->integer('price');
            $table->integer('discount_percentage')->nullable();
            $table->json('highlights')
                ->comment('Array of {title, description, order}');
            $table->json('policies')
                ->comment('Array of {title, content, type:(1=warrenty, 2=shipping, 3=return)}');
            $table->json('tags')->nullable();
            $table->tinyInteger('status')->default(0)
                ->comment('0=active, 1=inactive, 2=low stock, 3=sold out');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('brand_id')->references('id')->on('brands');
            $table->foreign('subcategory_id')->references('id')->on('sub_categories');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['subcategory_id']);
        });
        Schema::dropIfExists('products');
    }
};
