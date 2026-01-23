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
        Schema::create('deals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('description');
            $table->string('image');

            $table->tinyInteger('type')->comment('1=hot deals, 2=great deals, 3=flash sale');

            // Only required if type = 3 (flash sale)
            $table->integer('amount')->nullable()->comment('Required only if type=3');
            $table->date('start_date')->nullable()->comment('Required only if type=3');
            $table->date('end_date')->nullable()->comment('Required only if type=3');

            $table->tinyInteger('status')->default(0)->comment('0=active, 1=inactive');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
