<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBootsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('boots', function(Blueprint $table)
		{
			$table->increments('Id');
			$table->timestamp('Timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->string('Hour', 50)->nullable();
			$table->string('GroupId', 100)->nullable();
			$table->string('UserId', 100)->nullable();
			$table->string('MessageId', 100)->nullable();
			$table->integer('BootMinutes')->unsigned()->nullable();
			$table->string('BootReason', 50)->nullable();
			$table->dateTime('DateBooted')->nullable();
			$table->dateTime('DateToReadd')->nullable();
			$table->dateTime('DateReadded')->nullable();
			$table->text('AddResult', 65535)->nullable();
			$table->text('BootResult', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('boots');
	}

}
