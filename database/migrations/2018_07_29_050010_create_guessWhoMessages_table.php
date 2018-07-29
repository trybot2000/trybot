<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGuessWhoMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('guessWhoMessages', function(Blueprint $table)
		{
			$table->increments('Id');
			$table->timestamp('Timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->string('MessageId', 100);
			$table->text('Text', 65535);
			$table->integer('GameId')->unsigned();
			$table->integer('Used')->unsigned()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('guessWhoMessages');
	}

}
