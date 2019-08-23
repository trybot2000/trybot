<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('messages', function(Blueprint $table)
		{
			$table->integer('primary')->nullable();
			$table->string('id', 100)->unique('id');
			$table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->string('source_guid', 100);
			$table->string('created_at', 100)->index('created_at');
			$table->string('user_id', 100)->index('user_id');
			$table->string('group_id', 100)->index('group_id');
			$table->string('name', 100);
			$table->string('avatar_url', 254)->nullable();
			$table->text('text')->nullable();
			$table->string('system', 100);
			$table->string('mentions', 254)->nullable();
			$table->integer('numMentions')->unsigned()->nullable();
			$table->string('attachments_type', 254)->nullable();
			$table->string('attachments_url', 254)->nullable();
			$table->string('attachments_name', 254)->nullable();
			$table->string('attachments_lat', 254)->nullable();
			$table->string('attachments_lng', 254)->nullable();
			$table->integer('textLength')->unsigned()->nullable();
			$table->integer('numFavorites')->unsigned()->nullable()->index('numFavorites');
			$table->text('favoritedBy', 65535)->nullable();
			$table->integer('isTryBot')->unsigned()->default(0)->comment('0 or 1');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('messages');
	}

}
