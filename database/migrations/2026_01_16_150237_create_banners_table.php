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
        Schema::create('banners', function (Blueprint $table) {
            $table->increments('id');

            $table->string('title');

            $table->tinyInteger('type')->comment('1=home page, 2=hero page, 3=ads, 4=about page');
            $table->string('image');

            $table->tinyInteger('status')
                ->default(0)
                ->comment('0=active, 1=inactive');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
