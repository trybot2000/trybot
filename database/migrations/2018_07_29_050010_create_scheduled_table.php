<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScheduledTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scheduled', function(Blueprint $table)
		{
			$table->string('varTimeToSend', 254);
			$table->string('varTimeToSendPretty', 254)->comment('DATE_ISO8601 Format');
			$table->text('varText', 65535);
			$table->string('varSent', 12);
			$table->string('varSentAt', 254);
			$table->string('varSentAtDiff', 254);
			$table->string('varMessageName', 254);
			$table->string('varGroups', 254)->default('all');
			$table->primary(['varTimeToSend','varGroups']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scheduled');
	}

}
