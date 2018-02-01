<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    const TABLE = 'images';

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
            $table->string('original')->nullable();
            $table->json('sizes')->nullable();
            $table->json('available_sizes')->nullable()->comment('If null, all sizes will be created');
            $table->boolean('ready')->default(false);
            $table->unsignedInteger('width')->default(0);
            $table->unsignedInteger('height')->default(0);

            $table->unsignedInteger('offer_id')->nullable();
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('set null');
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
