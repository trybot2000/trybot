<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePalindromesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('palindromes', function(Blueprint $table)
		{
			$table->increments('primary');
			$table->string('id', 100)->nullable();
			$table->text('text', 65535)->nullable();
			$table->integer('length')->unsigned()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('palindromes');
	}

}
