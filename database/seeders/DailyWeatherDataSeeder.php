<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DailyWeatherDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder fills in missing/zero weather data fields with realistic values.
     */
    public function run()
    {
        $this->command->info('Populating missing/zero weather data fields...');

        // Get all records with missing or zero data
        $records = DB::table('daily_weathers')
            ->where(function ($query) {
                $query->whereNull('avg_temp')
                    ->orWhere('avg_temp', 0)
                    ->orWhereNull('humidity')
                    ->orWhere('humidity', 0)
                    ->orWhereNull('dry_bulb')
                    ->orWhere('dry_bulb', 0)
                    ->orWhereNull('dew_point')
                    ->orWhereNull('total_rain_fall')
                    ->orWhereNull('total_sunshine_hour')
                    ->orWhere('total_sunshine_hour', 0);
            })
            ->get();

        $count = 0;
        $total = $records->count();

        $this->command->info("Found {$total} records to update...");

        foreach ($records as $record) {
            $maxTemp = $record->max_temp ?? 30;
            $minTemp = $record->mini_temp ?? 20;

            // Ensure we have valid temps
            if ($maxTemp <= 0) $maxTemp = 30;
            if ($minTemp <= 0) $minTemp = 20;

            // Calculate realistic values based on existing data
            $avgTemp = ($record->avg_temp && $record->avg_temp > 0)
                ? $record->avg_temp
                : round(($maxTemp + $minTemp) / 2, 2);

            // Humidity varies by season - higher in summer monsoon months
            $month = (int) date('m', strtotime($record->record_date));
            $baseHumidity = $this->getBaseHumidity($month);

            $humidity = ($record->humidity && $record->humidity > 0)
                ? $record->humidity
                : round($baseHumidity + rand(-10, 10), 2);
            $humidity = max(30, min(100, $humidity)); // Keep between 30-100%

            // Dry bulb temperature is typically close to average temperature
            $dryBulb = ($record->dry_bulb && $record->dry_bulb > 0)
                ? $record->dry_bulb
                : round($avgTemp + rand(-2, 2) * 0.1, 2);

            // Dew point calculation based on humidity and temperature
            $dewPoint = $record->dew_point ?? $this->calculateDewPoint($avgTemp, $humidity);

            // Rainfall varies significantly by season - only update if NULL (0 is valid for no rain)
            $rainfall = $record->total_rain_fall ?? $this->getRainfall($month, $humidity);

            // Sunshine hours vary by season and weather - update if 0 or NULL
            $sunshineHours = ($record->total_sunshine_hour && $record->total_sunshine_hour > 0)
                ? $record->total_sunshine_hour
                : $this->getSunshineHours($month, $rainfall);

            DB::table('daily_weathers')
                ->where('id', $record->id)
                ->update([
                    'avg_temp' => $avgTemp,
                    'humidity' => $humidity,
                    'dry_bulb' => $dryBulb,
                    'dew_point' => $dewPoint,
                    'total_rain_fall' => $rainfall,
                    'total_sunshine_hour' => $sunshineHours,
                    'updated_at' => now(),
                ]);

            $count++;
            if ($count % 1000 === 0) {
                $this->command->info("Processed {$count} of {$total} records...");
            }
        }

        $this->command->info("Completed! Updated {$count} records.");
    }

    /**
     * Get base humidity based on month (Bangladesh climate)
     */
    private function getBaseHumidity(int $month): float
    {
        // Bangladesh climate: High humidity during monsoon (June-September)
        $humidityByMonth = [
            1 => 60,  // January - dry season
            2 => 55,  // February
            3 => 55,  // March
            4 => 60,  // April - pre-monsoon
            5 => 70,  // May
            6 => 82,  // June - monsoon starts
            7 => 88,  // July - peak monsoon
            8 => 88,  // August
            9 => 85,  // September
            10 => 78, // October - post-monsoon
            11 => 70, // November
            12 => 65, // December
        ];

        return $humidityByMonth[$month] ?? 70;
    }

    /**
     * Calculate dew point from temperature and humidity
     */
    private function calculateDewPoint(float $temp, float $humidity): float
    {
        // Magnus formula approximation
        $a = 17.27;
        $b = 237.7;
        $alpha = (($a * $temp) / ($b + $temp)) + log($humidity / 100);
        $dewPoint = ($b * $alpha) / ($a - $alpha);

        return round($dewPoint, 2);
    }

    /**
     * Get rainfall based on month and humidity
     */
    private function getRainfall(int $month, float $humidity): float
    {
        // Average daily rainfall by month (mm) - Bangladesh pattern
        $avgRainfallByMonth = [
            1 => 0.5,   // January - dry
            2 => 0.8,
            3 => 1.5,
            4 => 3.5,   // Pre-monsoon
            5 => 6.0,
            6 => 12.0,  // Monsoon
            7 => 15.0,  // Peak monsoon
            8 => 14.0,
            9 => 10.0,
            10 => 5.0,  // Post-monsoon
            11 => 1.5,
            12 => 0.5,
        ];

        $baseRainfall = $avgRainfallByMonth[$month] ?? 3.0;

        // Adjust based on humidity
        $humidityFactor = ($humidity > 80) ? 1.5 : (($humidity > 60) ? 1.0 : 0.5);

        // Add randomness (many days have 0 rain, some have heavy)
        $random = rand(0, 100);
        if ($random < 50) {
            // 50% chance of no rain
            return 0.00;
        } elseif ($random < 80) {
            // 30% chance of light rain
            return round($baseRainfall * $humidityFactor * rand(1, 100) / 100, 2);
        } else {
            // 20% chance of heavier rain
            return round($baseRainfall * $humidityFactor * rand(100, 300) / 100, 2);
        }
    }

    /**
     * Get sunshine hours based on month and rainfall
     */
    private function getSunshineHours(int $month, float $rainfall): float
    {
        // Max possible sunshine hours by month
        $maxSunshineByMonth = [
            1 => 10.5,
            2 => 11.0,
            3 => 11.5,
            4 => 12.0,
            5 => 12.5,
            6 => 12.5,
            7 => 12.5,
            8 => 12.0,
            9 => 11.5,
            10 => 11.0,
            11 => 10.5,
            12 => 10.0,
        ];

        $maxSunshine = $maxSunshineByMonth[$month] ?? 11.0;

        // Reduce sunshine on rainy days
        if ($rainfall > 10) {
            $factor = rand(15, 30) / 100; // Heavy rain - very little sunshine (1.5-3 hours)
        } elseif ($rainfall > 5) {
            $factor = rand(30, 50) / 100; // Moderate rain
        } elseif ($rainfall > 1) {
            $factor = rand(50, 70) / 100; // Light rain
        } elseif ($rainfall > 0) {
            $factor = rand(60, 80) / 100; // Very light rain
        } else {
            $factor = rand(70, 95) / 100; // Normal variation on dry days
        }

        return round($maxSunshine * $factor, 2);
    }
}
