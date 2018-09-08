<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRedditTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('reddit', function(Blueprint $table)
		{
			$table->string('subreddit', 254);
			$table->string('name', 254)->unique('name');
			$table->string('id', 50);
			$table->string('stickied', 254)->nullable();
			$table->string('author', 254);
			$table->string('url', 254);
			$table->string('tryBotPostedToGroupMe', 254)->nullable();
			$table->string('created_utc', 254);
			$table->string('title', 254);
			$table->text('selftext_html', 65535)->nullable();
			$table->text('selftext', 65535)->nullable();
			$table->string('isIntroPost', 10)->nullable();
			$table->string('tryBotRespondToIntroPost', 10)->nullable();
			$table->string('gamertag', 254)->nullable();
			$table->string('game', 254)->nullable();
			$table->text('botPostReturn', 65535)->nullable();
			$table->string('accountAge', 254)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('reddit');
	}

}
