<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->string('device_id');
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('build_id');
            $table->timestamps();

            $table->primary(['device_id', 'application_id']);

            $table->foreign('build_id')
                ->references('id')
                ->on('builds')
                ->onDelete('cascade');

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
        Schema::dropIfExists('devices');
    }
}
