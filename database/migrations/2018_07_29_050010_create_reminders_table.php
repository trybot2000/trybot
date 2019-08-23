<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRemindersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('reminders', function(Blueprint $table)
		{
			$table->integer('primary', true);
			$table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->dateTime('remindAt')->nullable();
			$table->integer('userId');
			$table->string('username');
			$table->enum('service', array('groupme','reddit'));
			$table->text('text', 65535);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('reminders');
	}

}
