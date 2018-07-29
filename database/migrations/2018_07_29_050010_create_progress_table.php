<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProgressTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('progress', function(Blueprint $table)
		{
			$table->string('progress', 254);
			$table->string('scriptName', 254);
			$table->integer('id', true);
			$table->string('current', 100);
			$table->string('target', 100);
			$table->string('memory', 254)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('progress');
	}

}
