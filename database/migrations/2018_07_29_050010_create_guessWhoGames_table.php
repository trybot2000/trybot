<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGuessWhoGamesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('guessWhoGames', function(Blueprint $table)
		{
			$table->increments('Id');
			$table->timestamp('Timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->string('GroupId', 50)->nullable();
			$table->string('UserIdInitiator', 50)->nullable();
			$table->string('MysteryUserId', 50)->nullable();
			$table->integer('IsActive')->unsigned()->nullable()->default(1);
			$table->integer('OmitFromStatistics')->unsigned()->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('guessWhoGames');
	}

}
