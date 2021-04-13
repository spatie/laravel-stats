<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatsTables extends Migration
{
    public function up()
    {
        Schema::create('stats_events', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('type');
            $table->bigInteger('value');

            $table->timestamps();
        });
    }
}
