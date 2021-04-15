<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('builds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('application_id');
            $table->string('platform');
            $table->string('version');
            $table->string('file');
            $table->boolean('forced')->default(false);
            $table->timestamp('available_from')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('application_id')
                ->references('id')
                ->on('applications');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('builds');
    }
}
