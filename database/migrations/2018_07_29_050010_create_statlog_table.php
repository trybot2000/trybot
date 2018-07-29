<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStatlogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('statlog', function(Blueprint $table)
		{
			$table->integer('primary', true);
			$table->string('strDateTimeLogged', 254);
			$table->string('intCp', 254)->default('0');
			$table->string('intCpTotal', 254)->default('0');
			$table->string('intCaps', 254)->default('0');
			$table->string('strPlace', 254)->default('0');
			$table->string('dtLastUpdatedWarSheet', 254)->nullable();
			$table->string('dtLastUpdatedPlayerSheet', 254);
			$table->string('arrScoreBoardScore1', 254)->nullable();
			$table->string('arrScoreBoardScore2', 254)->nullable();
			$table->string('arrScoreBoardScore3', 254)->nullable();
			$table->string('arrScoreBoardScore4', 254)->nullable();
			$table->string('arrScoreBoardScore5', 254)->nullable();
			$table->string('arrScoreBoardScore6', 254)->nullable();
			$table->string('arrScoreBoardScore7', 254)->nullable();
			$table->string('arrScoreBoardScore8', 254)->nullable();
			$table->string('arrScoreBoardScore9', 254)->nullable();
			$table->string('arrScoreBoardScore10', 254)->nullable();
			$table->string('arrScoreBoardScore11', 254)->nullable();
			$table->string('arrScoreBoardScore12', 254)->nullable();
			$table->string('arrScoreBoardName1', 254)->nullable();
			$table->string('arrScoreBoardName2', 254)->nullable();
			$table->string('arrScoreBoardName3', 254)->nullable();
			$table->string('arrScoreBoardName4', 254)->nullable();
			$table->string('arrScoreBoardName5', 254)->nullable();
			$table->string('arrScoreBoardName6', 254)->nullable();
			$table->string('arrScoreBoardName7', 254)->nullable();
			$table->string('arrScoreBoardName8', 254)->nullable();
			$table->string('arrScoreBoardName9', 254)->nullable();
			$table->string('arrScoreBoardName10', 254)->nullable();
			$table->string('arrScoreBoardName11', 254)->nullable();
			$table->string('arrScoreBoardName12', 254)->nullable();
			$table->string('numClans', 254)->nullable();
			$table->string('intTotalHours', 254)->default('0');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('statlog');
	}

}
