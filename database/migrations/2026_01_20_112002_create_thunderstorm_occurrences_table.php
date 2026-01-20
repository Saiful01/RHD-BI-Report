<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('thunderstorm_occurrences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('station_id');
            $table->date('occurrence_date');
            $table->tinyInteger('utc_hour')->nullable();
            $table->string('condition', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('station_id', 'thunderstorm_station_fk')->references('id')->on('stations')->onDelete('cascade');
            $table->index(['station_id', 'occurrence_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thunderstorm_occurrences');
    }
};
