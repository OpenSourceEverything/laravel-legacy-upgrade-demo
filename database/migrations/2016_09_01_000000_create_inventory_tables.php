<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryTables extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name', 120);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('category_id');
            $table->string('sku', 32);
            $table->string('name', 120);
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('price_cents')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'sku']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
        });
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->integer('delta');
            $table->string('reason', 200);
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('items');
        Schema::dropIfExists('categories');
    }
}
