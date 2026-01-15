<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Station;
use App\Models\DailyWeather;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportWeatherData extends Command
{
    protected $signature = 'weather:import';
    protected $description = 'Import Max and Min temperature from CSV files';

    public function handle()
    {

        $maxFile = storage_path('app/weather/All_Stations_Daily_Maximum_Temperature(°C).csv');
        $minFile = storage_path('app/weather/All_Stations_Daily_Minimum_Temperature(°C).csv');

        $this->info('Starting Import...');


        $this->processFile($maxFile, 'max_temp');


        $this->processFile($minFile, 'mini_temp');

        $this->info('All Data Imported Successfully!');
    }

    private function processFile($filePath, $columnName)
    {
        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return;
        }

        $file = fopen($filePath, 'r');


        for ($i = 0; $i < 12; $i++) fgetcsv($file);

        $rowCount = 0;
        while (($row = fgetcsv($file)) !== FALSE) {

            if (count($row) < 3) continue;

            $stationName = trim($row[0]);
            $year = $row[1];
            $month = $row[2];


            $station = Station::firstOrCreate(
                ['station_name' => $stationName],
                [
                    'lat' => rand(20000, 26000) / 1000,
                    'lon' => rand(88000, 92000) / 1000,
                    'status' => 'active'
                ]
            );


            for ($day = 1; $day <= 31; $day++) {
                $tempValue = isset($row[$day + 2]) ? trim($row[$day + 2]) : '';


                if ($tempValue !== '' && $tempValue !== '**') {
                    try {

                        if (!checkdate($month, $day, $year)) continue;

                        $recordDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');


                        DB::table('daily_weathers')->updateOrInsert(
                            ['station_id' => $station->id, 'record_date' => $recordDate],
                            [$columnName => (float)$tempValue, 'updated_at' => now()]
                        );

                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            $rowCount++;
            if($rowCount % 100 == 0) $this->line("Processed $rowCount rows for $columnName...");
        }
        fclose($file);
    }
}
