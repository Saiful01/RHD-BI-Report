<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ConstructionWeatherService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('weather_analytics');
    }

    /**
     * Calculate standard deviation
     */
    public function calculateStdDev(array $values, string $type = 'population'): float
    {
        $count = count($values);
        if ($count < 2) return 0;

        $mean = array_sum($values) / $count;
        $sumSquaredDiff = 0;

        foreach ($values as $value) {
            $sumSquaredDiff += pow($value - $mean, 2);
        }

        $divisor = $type === 'sample' ? ($count - 1) : $count;
        return sqrt($sumSquaredDiff / $divisor);
    }

    /**
     * Calculate reliability values
     */
    public function calculateReliability(float $mean, float $stdDev, string $type = '98', bool $isMin = false): float
    {
        $zScore = $type === '98'
            ? $this->config['analysis']['reliability_98_zscore']
            : $this->config['analysis']['reliability_50_zscore'];

        if ($isMin) {
            return round($mean - ($zScore * $stdDev), 2);
        }
        return round($mean + ($zScore * $stdDev), 2);
    }

    /**
     * Get season for a given month
     */
    public function getSeason(int $month): string
    {
        foreach ($this->config['seasons'] as $season => $months) {
            if (in_array($month, $months)) {
                return $season;
            }
        }
        return 'unknown';
    }

    /**
     * Calculate CSI from pre-aggregated scores (simplified for performance)
     */
    public function calculateCSIFromAggregates(float $avgRainfall, float $avgMaxTemp, float $avgMinTemp, float $avgHumidity, float $avgSunshine): array
    {
        $weights = $this->config['csi_weights'];

        // Simplified scoring based on averages
        $rainfallScore = $this->calculateRainfallScoreFromAvg($avgRainfall);
        $tempScore = $this->calculateTemperatureScoreFromAvg($avgMaxTemp, $avgMinTemp);
        $humidityScore = $this->calculateHumidityScoreFromAvg($avgHumidity);
        $sunshineScore = $this->calculateSunshineScoreFromAvg($avgSunshine);

        $csi = ($rainfallScore * $weights['rainfall']) +
               ($tempScore * $weights['temperature']) +
               ($humidityScore * $weights['humidity']) +
               ($sunshineScore * $weights['sunshine']);

        return [
            'csi' => round($csi, 1),
            'rainfall_score' => round($rainfallScore, 1),
            'temperature_score' => round($tempScore, 1),
            'humidity_score' => round($humidityScore, 1),
            'sunshine_score' => round($sunshineScore, 1),
            'rating' => $this->getCSIRating($csi),
        ];
    }

    protected function calculateRainfallScoreFromAvg(float $avgDailyRainfall): float
    {
        if ($avgDailyRainfall <= 0.5) return 100;
        if ($avgDailyRainfall <= 2) return 85;
        if ($avgDailyRainfall <= 5) return 70;
        if ($avgDailyRainfall <= 10) return 50;
        if ($avgDailyRainfall <= 20) return 30;
        return 10;
    }

    protected function calculateTemperatureScoreFromAvg(float $avgMax, float $avgMin): float
    {
        $avgTemp = ($avgMax + $avgMin) / 2;
        $thresholds = $this->config['temperature'];

        if ($avgMax > $thresholds['heat_danger']) return 20;
        if ($avgMax > $thresholds['heat_warning']) return 50;
        if ($avgMin < $thresholds['cold_warning']) return 60;
        if ($avgTemp >= $thresholds['optimal_min'] && $avgTemp <= $thresholds['optimal_max']) return 100;
        return 75;
    }

    protected function calculateHumidityScoreFromAvg(float $avgHumidity): float
    {
        $thresholds = $this->config['humidity'];
        if ($avgHumidity <= $thresholds['excellent_max']) return 100;
        if ($avgHumidity <= $thresholds['good_max']) return 85;
        if ($avgHumidity <= $thresholds['sealing_max']) return 70;
        if ($avgHumidity <= $thresholds['painting_max']) return 50;
        return 30;
    }

    protected function calculateSunshineScoreFromAvg(float $avgSunshine): float
    {
        $thresholds = $this->config['sunshine'];
        if ($avgSunshine >= $thresholds['excellent_min']) return 100;
        if ($avgSunshine >= $thresholds['productive_min']) return 85;
        if ($avgSunshine >= $thresholds['min_work']) return 60;
        return 40;
    }

    /**
     * Get CSI rating label
     */
    public function getCSIRating(float $csi): string
    {
        $ratings = $this->config['csi_ratings'];
        if ($csi >= $ratings['excellent']) return 'excellent';
        if ($csi >= $ratings['good']) return 'good';
        if ($csi >= $ratings['fair']) return 'fair';
        if ($csi >= $ratings['poor']) return 'poor';
        return 'not_recommended';
    }

    /**
     * Analyze construction weather data for a station - OPTIMIZED with DB aggregation
     */
    public function analyzeStationData(int $stationId, string $fromDate, string $toDate, string $sdType = 'population'): ?array
    {
        // Use database aggregation for yearly stats - much faster
        $yearlyStats = DB::table('daily_weathers')
            ->where('station_id', $stationId)
            ->whereBetween('record_date', [$fromDate, $toDate])
            ->whereNull('deleted_at')
            ->selectRaw('
                YEAR(record_date) as year,
                COUNT(*) as total_days,
                ROUND(AVG(max_temp), 2) as max_temp_avg,
                ROUND(MAX(max_temp), 2) as max_temp_max,
                ROUND(AVG(mini_temp), 2) as min_temp_avg,
                ROUND(MIN(mini_temp), 2) as min_temp_min,
                ROUND(SUM(total_rain_fall), 2) as total_rainfall,
                ROUND(AVG(humidity), 2) as avg_humidity,
                ROUND(AVG(total_sunshine_hour), 2) as avg_sunshine,
                ROUND(SUM(total_sunshine_hour), 2) as total_sunshine,
                SUM(CASE WHEN total_rain_fall <= ? THEN 1 ELSE 0 END) as dry_days,
                SUM(CASE WHEN total_rain_fall > ? THEN 1 ELSE 0 END) as rainy_days,
                SUM(CASE WHEN total_rain_fall > ? THEN 1 ELSE 0 END) as heavy_rain_days,
                SUM(CASE WHEN max_temp > ? THEN 1 ELSE 0 END) as hot_days,
                SUM(CASE WHEN max_temp > ? THEN 1 ELSE 0 END) as extreme_hot_days,
                SUM(CASE WHEN total_sunshine_hour >= ? THEN 1 ELSE 0 END) as productive_sunshine_days,
                SUM(CASE WHEN total_rain_fall <= ? AND max_temp <= ? AND mini_temp >= ? AND humidity <= ? THEN 1 ELSE 0 END) as working_days
            ', [
                $this->config['rainfall']['workable_daily_max'],
                $this->config['rainfall']['moderate'],
                $this->config['rainfall']['heavy'],
                $this->config['temperature']['heat_warning'],
                $this->config['temperature']['heat_danger'],
                $this->config['sunshine']['productive_min'],
                $this->config['rainfall']['workable_daily_max'],
                $this->config['temperature']['heat_warning'],
                $this->config['temperature']['cold_warning'],
                $this->config['humidity']['general_work_max']
            ])
            ->groupBy(DB::raw('YEAR(record_date)'))
            ->orderBy('year')
            ->get()
            ->toArray();

        if (empty($yearlyStats)) return null;

        // Convert to array format
        $yearlyData = array_map(function($row) {
            return (array) $row;
        }, $yearlyStats);

        return $this->calculateMultiYearStats($yearlyData, $sdType);
    }

    /**
     * Calculate multi-year statistics with reliability metrics
     */
    protected function calculateMultiYearStats(array $yearlyStats, string $sdType): array
    {
        $maxTempAvgs = array_column($yearlyStats, 'max_temp_avg');
        $maxTempMaxes = array_column($yearlyStats, 'max_temp_max');
        $minTempAvgs = array_column($yearlyStats, 'min_temp_avg');
        $minTempMins = array_column($yearlyStats, 'min_temp_min');
        $totalRainfalls = array_column($yearlyStats, 'total_rainfall');
        $avgHumidities = array_filter(array_column($yearlyStats, 'avg_humidity'));
        $avgSunshines = array_filter(array_column($yearlyStats, 'avg_sunshine'));
        $dryDays = array_column($yearlyStats, 'dry_days');
        $workingDays = array_column($yearlyStats, 'working_days');

        // Calculate means
        $meanMaxTempAvg = array_sum($maxTempAvgs) / count($maxTempAvgs);
        $meanMaxTempMax = array_sum($maxTempMaxes) / count($maxTempMaxes);
        $meanMinTempAvg = array_sum($minTempAvgs) / count($minTempAvgs);
        $meanMinTempMin = array_sum($minTempMins) / count($minTempMins);
        $meanRainfall = array_sum($totalRainfalls) / count($totalRainfalls);
        $meanHumidity = !empty($avgHumidities) ? array_sum($avgHumidities) / count($avgHumidities) : null;
        $meanSunshine = !empty($avgSunshines) ? array_sum($avgSunshines) / count($avgSunshines) : null;
        $meanDryDays = array_sum($dryDays) / count($dryDays);
        $meanWorkingDays = array_sum($workingDays) / count($workingDays);

        // Calculate standard deviations
        $stdMaxTempAvg = $this->calculateStdDev($maxTempAvgs, $sdType);
        $stdMaxTempMax = $this->calculateStdDev($maxTempMaxes, $sdType);
        $stdMinTempAvg = $this->calculateStdDev($minTempAvgs, $sdType);
        $stdMinTempMin = $this->calculateStdDev($minTempMins, $sdType);
        $stdRainfall = $this->calculateStdDev($totalRainfalls, $sdType);
        $stdDryDays = $this->calculateStdDev($dryDays, $sdType);
        $stdWorkingDays = $this->calculateStdDev($workingDays, $sdType);

        return [
            'years_analyzed' => count($yearlyStats),
            'yearly_data' => $yearlyStats,

            'max_temp_avg' => [
                'mean' => round($meanMaxTempAvg, 1),
                'std' => round($stdMaxTempAvg, 1),
                'reliability_50' => round($meanMaxTempAvg, 1),
                'reliability_98' => $this->calculateReliability($meanMaxTempAvg, $stdMaxTempAvg, '98', false),
            ],
            'max_temp_extreme' => [
                'mean' => round($meanMaxTempMax, 1),
                'std' => round($stdMaxTempMax, 1),
                'reliability_50' => round($meanMaxTempMax, 1),
                'reliability_98' => $this->calculateReliability($meanMaxTempMax, $stdMaxTempMax, '98', false),
            ],
            'min_temp_avg' => [
                'mean' => round($meanMinTempAvg, 1),
                'std' => round($stdMinTempAvg, 1),
                'reliability_50' => round($meanMinTempAvg, 1),
                'reliability_98' => $this->calculateReliability($meanMinTempAvg, $stdMinTempAvg, '98', true),
            ],
            'min_temp_extreme' => [
                'mean' => round($meanMinTempMin, 1),
                'std' => round($stdMinTempMin, 1),
                'reliability_50' => round($meanMinTempMin, 1),
                'reliability_98' => $this->calculateReliability($meanMinTempMin, $stdMinTempMin, '98', true),
            ],

            'annual_rainfall' => [
                'mean' => round($meanRainfall, 1),
                'std' => round($stdRainfall, 1),
                'reliability_50' => round($meanRainfall, 1),
                'reliability_98' => $this->calculateReliability($meanRainfall, $stdRainfall, '98', false),
            ],

            'avg_humidity' => $meanHumidity ? round($meanHumidity, 1) : null,
            'avg_sunshine' => $meanSunshine ? round($meanSunshine, 1) : null,

            'dry_days' => [
                'mean' => round($meanDryDays, 0),
                'std' => round($stdDryDays, 1),
                'reliability_50' => round($meanDryDays, 0),
                'reliability_98' => round($this->calculateReliability($meanDryDays, $stdDryDays, '98', true), 0),
            ],
            'working_days' => [
                'mean' => round($meanWorkingDays, 0),
                'std' => round($stdWorkingDays, 1),
                'reliability_50' => round($meanWorkingDays, 0),
                'reliability_98' => round($this->calculateReliability($meanWorkingDays, $stdWorkingDays, '98', true), 0),
            ],
        ];
    }

    /**
     * Calculate monthly analysis for a station - OPTIMIZED with DB aggregation
     */
    public function analyzeMonthlyData(int $stationId, string $fromDate, string $toDate, string $sdType = 'population'): array
    {
        // Single query to get all monthly data grouped by year-month
        $monthlyData = DB::table('daily_weathers')
            ->where('station_id', $stationId)
            ->whereBetween('record_date', [$fromDate, $toDate])
            ->whereNull('deleted_at')
            ->selectRaw('
                YEAR(record_date) as year,
                MONTH(record_date) as month,
                COUNT(*) as total_days,
                ROUND(AVG(max_temp), 2) as avg_max_temp,
                ROUND(AVG(mini_temp), 2) as avg_min_temp,
                ROUND(SUM(total_rain_fall), 2) as total_rainfall,
                ROUND(AVG(total_rain_fall), 2) as avg_rainfall,
                ROUND(AVG(humidity), 2) as avg_humidity,
                ROUND(AVG(total_sunshine_hour), 2) as avg_sunshine,
                SUM(CASE WHEN total_rain_fall <= ? THEN 1 ELSE 0 END) as dry_days
            ', [$this->config['rainfall']['workable_daily_max']])
            ->groupBy(DB::raw('YEAR(record_date)'), DB::raw('MONTH(record_date)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        if ($monthlyData->isEmpty()) return [];

        // Group by month across all years
        $monthGroups = [];
        foreach ($monthlyData as $row) {
            $month = $row->month;
            if (!isset($monthGroups[$month])) {
                $monthGroups[$month] = [];
            }
            $monthGroups[$month][] = (array) $row;
        }

        $monthlyStats = [];
        foreach ($monthGroups as $month => $yearlyMonthData) {
            if (empty($yearlyMonthData)) continue;

            $avgMaxTemps = array_column($yearlyMonthData, 'avg_max_temp');
            $avgMinTemps = array_column($yearlyMonthData, 'avg_min_temp');
            $totalRainfalls = array_column($yearlyMonthData, 'total_rainfall');
            $dryDaysCounts = array_column($yearlyMonthData, 'dry_days');

            $meanMaxTemp = array_sum($avgMaxTemps) / count($avgMaxTemps);
            $meanMinTemp = array_sum($avgMinTemps) / count($avgMinTemps);
            $meanRainfall = array_sum($totalRainfalls) / count($totalRainfalls);
            $meanDryDays = array_sum($dryDaysCounts) / count($dryDaysCounts);

            $stdMaxTemp = $this->calculateStdDev($avgMaxTemps, $sdType);
            $stdMinTemp = $this->calculateStdDev($avgMinTemps, $sdType);
            $stdRainfall = $this->calculateStdDev($totalRainfalls, $sdType);
            $stdDryDays = $this->calculateStdDev($dryDaysCounts, $sdType);

            $avgHumidity = collect($yearlyMonthData)->pluck('avg_humidity')->filter()->avg() ?? 70;
            $avgSunshine = collect($yearlyMonthData)->pluck('avg_sunshine')->filter()->avg() ?? 6;
            $avgDailyRainfall = $meanRainfall / 30;

            $csi = $this->calculateCSIFromAggregates($avgDailyRainfall, $meanMaxTemp, $meanMinTemp, $avgHumidity, $avgSunshine);

            $monthlyStats[$month] = [
                'month' => $month,
                'month_name' => $this->config['months'][$month]['en'],
                'month_short' => $this->config['months'][$month]['short'],
                'season' => $this->getSeason($month),
                'season_name' => $this->config['season_names'][$this->getSeason($month)] ?? 'Unknown',
                'years_analyzed' => count($yearlyMonthData),
                'yearly_data' => $yearlyMonthData,

                'max_temp' => [
                    'mean' => round($meanMaxTemp, 1),
                    'std' => round($stdMaxTemp, 1),
                    'reliability_50' => round($meanMaxTemp, 1),
                    'reliability_98' => $this->calculateReliability($meanMaxTemp, $stdMaxTemp, '98', false),
                ],
                'min_temp' => [
                    'mean' => round($meanMinTemp, 1),
                    'std' => round($stdMinTemp, 1),
                    'reliability_50' => round($meanMinTemp, 1),
                    'reliability_98' => $this->calculateReliability($meanMinTemp, $stdMinTemp, '98', true),
                ],
                'rainfall' => [
                    'mean' => round($meanRainfall, 1),
                    'std' => round($stdRainfall, 1),
                    'reliability_50' => round($meanRainfall, 1),
                    'reliability_98' => $this->calculateReliability($meanRainfall, $stdRainfall, '98', false),
                ],
                'dry_days' => [
                    'mean' => round($meanDryDays, 0),
                    'std' => round($stdDryDays, 1),
                    'reliability_50' => round($meanDryDays, 0),
                    'reliability_98' => round($this->calculateReliability($meanDryDays, $stdDryDays, '98', true), 0),
                ],
                'avg_humidity' => round($avgHumidity, 1),
                'avg_sunshine' => round($avgSunshine, 1),
                'csi' => $csi,
            ];
        }

        // Sort by month number to ensure correct order
        ksort($monthlyStats);

        return $monthlyStats;
    }

    /**
     * Get recommended construction periods
     */
    public function getRecommendedPeriods(array $monthlyStats): array
    {
        $excellent = [];
        $good = [];
        $fair = [];
        $avoid = [];

        foreach ($monthlyStats as $month => $stats) {
            $csi = $stats['csi']['csi'];
            $monthName = $stats['month_short'];

            if ($csi >= $this->config['csi_ratings']['excellent']) {
                $excellent[] = $monthName;
            } elseif ($csi >= $this->config['csi_ratings']['good']) {
                $good[] = $monthName;
            } elseif ($csi >= $this->config['csi_ratings']['fair']) {
                $fair[] = $monthName;
            } else {
                $avoid[] = $monthName;
            }
        }

        return [
            'excellent' => $excellent,
            'good' => $good,
            'fair' => $fair,
            'avoid' => $avoid,
        ];
    }
}
