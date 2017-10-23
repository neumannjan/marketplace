<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    const TABLE = 'users';
    const SEARCH_INDEX = 'i_users_search';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('activation_token');
            $table->string('display_name')->nullable();
            $table->string('options')->nullable();
            $table->enum('status', ['inactive', 'active', 'banned']);
            $table->rememberToken();
            $table->timestamps();

            $table->index(['email', 'username', 'display_name'], self::SEARCH_INDEX);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE);
    }
}
