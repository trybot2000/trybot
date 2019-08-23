<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTwitchTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('flaversham.twitch', function(Blueprint $table)
		{
			$table->integer('user_id')->unsigned()->nullable();
			$table->string('twitch_username')->nullable();
			$table->string('twitch_user_id')->nullable();
			$table->integer('is_active')->unsigned()->nullable()->default(1);
			$table->timestamps();
			$table->increments('id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('flaversham.twitch');
	}

}
