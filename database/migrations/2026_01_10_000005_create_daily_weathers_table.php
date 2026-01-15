<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyWeathersTable extends Migration
{
    public function up()
    {
        Schema::create('daily_weathers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('station_id');

            $table->date('record_date');
            $table->decimal('max_temp', 5, 2)->nullable();
            $table->decimal('mini_temp', 5, 2)->nullable();
            $table->decimal('avg_temp', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('dry_bulb', 5, 2)->nullable();
            $table->decimal('dew_point', 5, 2)->nullable();
            $table->decimal('total_rain_fall', 5, 2)->nullable();
            $table->decimal('total_sunshine_hour', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('station_id', 'station_fk_10789428')->references('id')->on('stations')->onDelete('cascade');


            $table->unique(['station_id', 'record_date'], 'station_date_unique');


            $table->index(['station_id', 'record_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_weathers');
    }
}
