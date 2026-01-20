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
        Schema::table('daily_weathers', function (Blueprint $table) {
            $table->decimal('wet_bulb', 5, 2)->nullable()->after('dew_point');
            $table->decimal('cloud_cover', 5, 2)->nullable()->after('wet_bulb');
            $table->decimal('mean_sea_level_pressure', 8, 2)->nullable()->after('cloud_cover');
            $table->decimal('station_level_pressure', 8, 2)->nullable()->after('mean_sea_level_pressure');
            $table->decimal('max_wind', 5, 2)->nullable()->after('station_level_pressure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_weathers', function (Blueprint $table) {
            $table->dropColumn(['wet_bulb', 'cloud_cover', 'mean_sea_level_pressure', 'station_level_pressure', 'max_wind']);
        });
    }
};
