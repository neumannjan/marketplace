<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for 'offers' table creation
 */
class CreateOffersTable extends Migration
{
    /**
     * Table name
     */
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
            $table->timestamp('listed_at')->useCurrent();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('author_user_id');
            $table->unsignedTinyInteger('status')->comment('0 == inactive, 1 == available, 2 == sold');
            $table->unsignedInteger('sold_to_user_id')->nullable();
            $table->unsignedInteger('price_value')->nullable();
            $table->unsignedInteger('bumped_times')->comment('2 max')->default(0);
            $table->string('currency_code', 3)->nullable();
            $table->unsignedInteger('reported_times')->default(0);

            $table->foreign('author_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sold_to_user_id')->references('id')->on('users')->onDelete('set null');
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
