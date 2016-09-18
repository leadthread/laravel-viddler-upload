<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVideosTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('viddler.table'), function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('mime');
            $table->string('path')->default('/');
            $table->string('disk');
            $table->string('filename');
            $table->string('title');
            $table->string('extension');
            $table->string('status')->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(config('viddler.table'));
    }
}
