<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMentionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mentions', function(Blueprint $table)
		{
			$table->integer('primary', true);
			$table->string('varId', 254)->unique('varId');
			$table->string('varUserId', 256);
			$table->string('varUserName', 254);
			$table->string('varDateTime', 254);
			$table->text('varText', 15000);
			$table->string('varGroupId', 254);
			$table->string('mentionType', 254)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mentions');
	}

}
