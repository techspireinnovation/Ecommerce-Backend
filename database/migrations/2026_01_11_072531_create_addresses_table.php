<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id')->nullable();
            $table->tinyInteger('type')->comment('1=user, 2=site, 3=shipping');
            $table->string('label', 50)->nullable();
            $table->string('street');
            $table->string('city', 100);
            $table->string('district', 100);
            $table->string('province', 100);
            $table->string('zip', 20)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=active, 1=inactive');
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('type');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['type']);
        });
        Schema::dropIfExists('addresses');
    }
};
