<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('auth', function(Blueprint $table)
		{
			$table->integer('primary', true);
			$table->string('authToken', 254);
			$table->string('userToken', 254)->comment('internal use');
			$table->string('service', 254);
			$table->string('userId', 150)->nullable();
			$table->integer('expiresAt');
			$table->unique(['authToken','userToken','service'], 'authToken');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('auth');
	}

}
