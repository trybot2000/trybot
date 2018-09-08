<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChatKemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('chatKems', function(Blueprint $table)
		{
			$table->increments('primary');
			$table->timestamp('timestamp')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->dateTime('timestampMessage')->nullable();
			$table->string('messageId')->nullable()->unique('messageId');
			$table->string('name')->nullable();
			$table->integer('userId')->nullable();
			$table->integer('createdAt')->unsigned()->nullable();
			$table->integer('groupId')->unsigned()->nullable();
			$table->integer('sentToGroup')->unsigned()->nullable()->default(0);
			$table->dateTime('timestampSent')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('chatKems');
	}

}
