<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTwitterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('twitter', function(Blueprint $table)
		{
			$table->string('varTimeTweetCreatedStamp', 254);
			$table->string('varTimeTweetCreated', 254)->comment('DATE_ISO8601 Format');
			$table->string('varTweetId', 254)->unique('varTweetId');
			$table->text('varText', 65535);
			$table->string('varSentAt', 254);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('twitter');
	}

}
