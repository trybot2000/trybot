<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePollTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('poll', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('timestamp', 254);
			$table->string('strPollName', 254);
			$table->string('IP', 254);
			$table->string('useragent', 254);
			$table->string('answer1', 254)->nullable()->default('0');
			$table->string('answer2', 254)->nullable()->default('0');
			$table->string('answer3', 254)->nullable()->default('0');
			$table->string('answer4', 254)->nullable()->default('0');
			$table->string('answer5', 254)->nullable()->default('0');
			$table->string('answer6', 254)->nullable()->default('0');
			$table->string('answer7', 254)->nullable()->default('0');
			$table->string('answer8', 254)->nullable()->default('0');
			$table->unique(['strPollName','IP'], 'strPollName');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('poll');
	}

}
