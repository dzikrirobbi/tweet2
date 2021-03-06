<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTweetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tweets', function (Blueprint $table) {
            $table->string('id');
            $table->text('json');
            $table->string('tweet_text')->nullable();
            $table->string('user_id')->nullable();
            $table->string('user_screen_name')->nullable();
            $table->string('user_avatar_url')->nullable();
            $table->string('geo')->nullable();
            $table->string('coordinates')->nullable();
            $table->string('places')->nullable();
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
        Schema::drop('tweets');
    }
}
