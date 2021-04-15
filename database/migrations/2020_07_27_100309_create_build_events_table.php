<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('build_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('build_id');
            $table->string('event');
            $table->string('user_agent');
            $table->timestamps();

            $table->foreign('build_id')
                ->references('id')
                ->on('builds')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('build_events');
    }
}
