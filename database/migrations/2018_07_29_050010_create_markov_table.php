<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMarkovTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('markov', function(Blueprint $table)
		{
			$table->increments('Id');
			$table->timestamp('Timestamp')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->char('Type', 50)->nullable();
			$table->text('Text', 65535)->nullable();
			$table->string('UserId', 50)->nullable();
			$table->string('GroupId', 50)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('markov');
	}

}
