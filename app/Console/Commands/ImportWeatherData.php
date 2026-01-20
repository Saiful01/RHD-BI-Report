<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportWeatherData extends Command
{
    protected $signature = 'weather:import {--clear : Clear existing data before import}';
    protected $description = 'Import weather data from CSV files in ref/weather-data folder';

    private $stationCache = [];
    private $dataPath;

    // Map CSV files to database columns
    private $fileColumnMap = [
        'All_Stations_Daily_Maximum_Temperature(°C).csv' => 'max_temp',
        'All_Stations_Daily_Minimum_Temperature(°C).csv' => 'mini_temp',
        'All_Stations_Daily_Average_Dry-bulb_Temperature(°C).csv' => 'dry_bulb',
        'All_Stations_Daily_Average_Dew-point_Temperature(°C).csv' => 'dew_point',
        'All_Stations_Daily_Average_Wet-bulb_Temperature(°C).csv' => 'wet_bulb',
        'All_Stations_Daily_Average_Humidity (%).csv' => 'humidity',
        'All_Stations_Daily_Total_Rainfall(mm).csv' => 'total_rain_fall',
        'All_Stations_Daily_Total_Sunshine_Hours.csv' => 'total_sunshine_hour',
        'All_Stations_Daily_Average_Cloud_in_Octa.csv' => 'cloud_cover',
        'All_Stations_Daily_Average_Mean_Sea_Level_Pressure(hpa).csv' => 'mean_sea_level_pressure',
        'All_Stations_Daily_Average_Station_Level_Pressure(hpa).csv' => 'station_level_pressure',
        'All_Stations_Daily_Max_Wind(kts).csv' => 'max_wind',
    ];

    public function handle()
    {
        $this->dataPath = base_path('ref/weather-data');

        if (!is_dir($this->dataPath)) {
            $this->error("Data directory not found: {$this->dataPath}");
            return 1;
        }

        // Load station cache
        $this->loadStationCache();

        // Clear existing data if requested
        if ($this->option('clear')) {
            $this->clearExistingData();
        }

        $this->info('Starting weather data import...');
        $this->newLine();

        // Process each weather data file
        foreach ($this->fileColumnMap as $filename => $column) {
            $filePath = $this->dataPath . '/' . $filename;
            if (file_exists($filePath)) {
                $this->processWeatherFile($filePath, $column);
            } else {
                $this->warn("File not found: $filename");
            }
        }

        // Calculate avg_temp from max and min
        $this->info('Calculating average temperatures...');
        $this->calculateAvgTemp();

        // Process thunderstorm data
        $thunderstormFile = $this->dataPath . '/Thunderstorm_occurrence.csv';
        if (file_exists($thunderstormFile)) {
            $this->processThunderstormFile($thunderstormFile);
        }

        $this->newLine();
        $this->info('Weather data import completed successfully!');

        // Show summary
        $this->showSummary();

        return 0;
    }

    private function loadStationCache()
    {
        $this->info('Loading station data...');
        $stations = DB::table('stations')->select('id', 'station_name')->get();
        foreach ($stations as $station) {
            $this->stationCache[strtolower(trim($station->station_name))] = $station->id;
        }
        $this->info('Loaded ' . count($this->stationCache) . ' stations');
    }

    private function getStationId($stationName)
    {
        $key = strtolower(trim($stationName));
        return $this->stationCache[$key] ?? null;
    }

    private function clearExistingData()
    {
        $this->warn('Clearing existing weather data...');

        DB::table('daily_weathers')->truncate();
        $this->info('Cleared daily_weathers table');

        DB::table('thunderstorm_occurrences')->truncate();
        $this->info('Cleared thunderstorm_occurrences table');
    }

    private function processWeatherFile($filePath, $columnName)
    {
        $filename = basename($filePath);
        $this->info("Processing: $filename -> $columnName");

        $file = fopen($filePath, 'r');
        if (!$file) {
            $this->error("Cannot open file: $filePath");
            return;
        }

        // Skip header rows (find the row starting with "Station")
        $headerFound = false;
        $lineNumber = 0;
        while (($row = fgetcsv($file)) !== FALSE && $lineNumber < 20) {
            $lineNumber++;
            if (isset($row[0]) && trim($row[0]) === 'Station') {
                $headerFound = true;
                break;
            }
        }

        if (!$headerFound) {
            $this->error("Header row not found in: $filename");
            fclose($file);
            return;
        }

        $rowCount = 0;
        $insertedCount = 0;
        $skippedCount = 0;
        $batchData = [];
        $batchSize = 1000;

        $progressBar = $this->output->createProgressBar();
        $progressBar->start();

        while (($row = fgetcsv($file)) !== FALSE) {
            if (count($row) < 4) continue;

            $stationName = trim($row[0]);
            if (empty($stationName) || $stationName === 'Station') continue;

            $stationId = $this->getStationId($stationName);
            if (!$stationId) {
                $skippedCount++;
                continue;
            }

            $year = (int)$row[1];
            $month = (int)$row[2];

            if ($year < 1900 || $year > 2100 || $month < 1 || $month > 12) {
                continue;
            }

            // Process each day (Day1 to Day31 are in columns 3 to 33)
            for ($day = 1; $day <= 31; $day++) {
                $colIndex = $day + 2; // Day1 is at index 3
                $value = isset($row[$colIndex]) ? trim($row[$colIndex]) : '';

                // Skip empty, missing, or invalid values
                if ($value === '' || $value === '**' || $value === '-' || !is_numeric($value)) {
                    continue;
                }

                // Validate date
                if (!checkdate($month, $day, $year)) {
                    continue;
                }

                $recordDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

                $batchData[] = [
                    'station_id' => $stationId,
                    'record_date' => $recordDate,
                    'column' => $columnName,
                    'value' => (float)$value
                ];

                $insertedCount++;
            }

            $rowCount++;

            // Process batch
            if (count($batchData) >= $batchSize) {
                $this->insertBatch($batchData);
                $batchData = [];
                $progressBar->advance($batchSize);
            }
        }

        // Insert remaining batch
        if (!empty($batchData)) {
            $this->insertBatch($batchData);
            $progressBar->advance(count($batchData));
        }

        $progressBar->finish();
        $this->newLine();

        fclose($file);
        $this->line("  -> Processed $rowCount rows, inserted $insertedCount values, skipped $skippedCount unknown stations");
    }

    private function insertBatch($batchData)
    {
        // Group by station_id and record_date for upsert
        $grouped = [];
        foreach ($batchData as $item) {
            $key = $item['station_id'] . '_' . $item['record_date'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'station_id' => $item['station_id'],
                    'record_date' => $item['record_date'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $grouped[$key][$item['column']] = $item['value'];
        }

        // Upsert in chunks
        $chunks = array_chunk(array_values($grouped), 100);
        foreach ($chunks as $chunk) {
            DB::table('daily_weathers')->upsert(
                $chunk,
                ['station_id', 'record_date'],
                array_keys($chunk[0])
            );
        }
    }

    private function calculateAvgTemp()
    {
        // Update avg_temp where both max_temp and mini_temp exist
        $updated = DB::table('daily_weathers')
            ->whereNotNull('max_temp')
            ->whereNotNull('mini_temp')
            ->whereNull('avg_temp')
            ->update([
                'avg_temp' => DB::raw('ROUND((max_temp + mini_temp) / 2, 2)')
            ]);

        $this->info("  -> Updated $updated records with calculated avg_temp");
    }

    private function processThunderstormFile($filePath)
    {
        $this->info('Processing: Thunderstorm_occurrence.csv');

        $file = fopen($filePath, 'r');
        if (!$file) {
            $this->error("Cannot open thunderstorm file");
            return;
        }

        // Skip header rows
        $headerFound = false;
        $lineNumber = 0;
        while (($row = fgetcsv($file)) !== FALSE && $lineNumber < 20) {
            $lineNumber++;
            if (isset($row[0]) && trim($row[0]) === 'Station') {
                $headerFound = true;
                break;
            }
        }

        if (!$headerFound) {
            $this->error("Header row not found in thunderstorm file");
            fclose($file);
            return;
        }

        $insertedCount = 0;
        $skippedCount = 0;
        $batchData = [];

        while (($row = fgetcsv($file)) !== FALSE) {
            if (count($row) < 5) continue;

            $stationName = trim($row[0]);
            if (empty($stationName) || $stationName === 'Station') continue;

            // Try to match station name (thunderstorm file uses short names)
            $stationId = $this->getStationId($stationName);

            // Try with (CTG) suffix for Ambagan
            if (!$stationId && $stationName === 'Ambagan') {
                $stationId = $this->getStationId('Ambagan(CTG)');
            }

            if (!$stationId) {
                $skippedCount++;
                continue;
            }

            $year = (int)$row[1];
            $month = (int)$row[2];
            $day = (int)$row[3];
            $utc = isset($row[4]) ? (int)$row[4] : null;
            $condition = isset($row[5]) ? trim($row[5]) : 'Thunderstorm';

            if (!checkdate($month, $day, $year)) {
                continue;
            }

            $occurrenceDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

            $batchData[] = [
                'station_id' => $stationId,
                'occurrence_date' => $occurrenceDate,
                'utc_hour' => $utc,
                'condition' => $condition,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $insertedCount++;

            if (count($batchData) >= 500) {
                DB::table('thunderstorm_occurrences')->insert($batchData);
                $batchData = [];
            }
        }

        // Insert remaining
        if (!empty($batchData)) {
            DB::table('thunderstorm_occurrences')->insert($batchData);
        }

        fclose($file);
        $this->line("  -> Inserted $insertedCount thunderstorm occurrences, skipped $skippedCount unknown stations");
    }

    private function showSummary()
    {
        $this->newLine();
        $this->info('=== Import Summary ===');

        $dailyCount = DB::table('daily_weathers')->count();
        $this->line("Daily weather records: " . number_format($dailyCount));

        $thunderstormCount = DB::table('thunderstorm_occurrences')->count();
        $this->line("Thunderstorm occurrences: " . number_format($thunderstormCount));

        $dateRange = DB::table('daily_weathers')
            ->selectRaw('MIN(record_date) as min_date, MAX(record_date) as max_date')
            ->first();

        if ($dateRange->min_date) {
            $this->line("Date range: {$dateRange->min_date} to {$dateRange->max_date}");
        }

        $stationCount = DB::table('daily_weathers')->distinct('station_id')->count('station_id');
        $this->line("Stations with data: $stationCount");
    }
}
