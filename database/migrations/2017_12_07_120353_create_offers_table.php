<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration
{
    const TABLE = "offers";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->text('description');
            $table->unsignedInteger('author');
            $table->unsignedTinyInteger('status')->comment('0 == inactive, 1 == available, 2 == sold');
            $table->unsignedInteger('sold_to')->nullable();

            $table->foreign('author')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sold_to')->references('id')->on('users')->onDelete('set null');
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
