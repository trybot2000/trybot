<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConversationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('conversations', function(Blueprint $table)
		{
			$table->string('varDateStart', 254);
			$table->string('varDateEnd', 254);
			$table->string('varActive', 12);
			$table->string('varName', 12);
			$table->text('varInitialMessage', 65535);
			$table->string('varUserId', 254);
			$table->string('groupId', 254);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('conversations');
	}

}
