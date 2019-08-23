<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlackFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slack_user_name')->nullable();
            $table->string('slack_user_id')->nullable();
            $table->string('slack_team_id')->nullable();
            $table->string('slack_team_domain')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('slack_user_name');
            $table->dropColumn('slack_user_id');
            $table->dropColumn('slack_team_id');
            $table->dropColumn('slack_team_domain');
        });
    }
}
