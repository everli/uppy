<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartialRolloutsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('builds', function (Blueprint $table) {
            $table->boolean('partial_rollout')
                ->after('forced')
                ->default(false);

            $table->unsignedInteger('rollout_percentage')
                ->after('partial_rollout')
                ->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('builds', function (Blueprint $table) {
            $table->dropColumn('partial_rollout');
            $table->dropColumn('rollout_percentage');
        });
    }
}
