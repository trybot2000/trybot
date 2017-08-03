<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlackEmojiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slack.emoji', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->text('url');
            $table->string('aliasFor')->nullable()->default(null);
            $table->boolean('isActive');

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
        Schema::dropIfExists('slack.emoji');
    }
}
