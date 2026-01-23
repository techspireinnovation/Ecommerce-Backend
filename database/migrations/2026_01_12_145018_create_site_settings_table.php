<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('store_name', 100);
            $table->string('primary_mobile_no', 10);
            $table->string('secondary_mobile_no', 10)->nullable();
            $table->string('primary_email', 100);
            $table->string('secondary_email', 100)->nullable();
            $table->unsignedInteger('address_id');
            $table->string('logo_image');
            $table->string('fav_icon_image');
            $table->string('instagram_link')->nullable();
            $table->string('facebook_link')->nullable();
            $table->string('whatsapp_link')->nullable();
            $table->string('linkedin_link')->nullable();
            $table->timestamps(); 

            // Indexes
            $table->index('address_id');

            // Foreign key
            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->onDelete('cascade'); // optional, remove or use nullOnDelete if needed
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropIndex(['address_id']);
        });

        Schema::dropIfExists('site_settings');
    }
};
