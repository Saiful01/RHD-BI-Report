<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Services\ConstructionWeatherService;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ConstructionWeatherController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stations = Station::orderBy('station_name')->pluck('station_name', 'id');

        // Get date range for the filter
        $dateRange = DB::table('daily_weathers')
            ->whereNull('deleted_at')
            ->selectRaw('MIN(record_date) as min_date, MAX(record_date) as max_date')
            ->first();

        return view('admin.constructionWeather.index', compact('stations', 'dateRange'));
    }

    public function analysisData(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $service = new ConstructionWeatherService();

        // Get parameters
        $stationIds = $request->get('stations', []);
        if (is_string($stationIds)) {
            $stationIds = array_filter(explode(',', $stationIds));
        }

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $sdType = $request->get('sd_type', 'population');

        // If no stations selected, use all stations
        if (empty($stationIds)) {
            $stationIds = Station::pluck('id')->toArray();
        }

        // If no date range provided, use full available range
        if (empty($fromDate) || empty($toDate)) {
            $dateRange = DB::table('daily_weathers')
                ->whereNull('deleted_at')
                ->selectRaw('MIN(record_date) as min_date, MAX(record_date) as max_date')
                ->first();

            $fromDate = $fromDate ?: ($dateRange->min_date ?? date('Y-01-01'));
            $toDate = $toDate ?: ($dateRange->max_date ?? date('Y-12-31'));
        }

        $results = [];
        $config = config('weather_analytics');

        foreach ($stationIds as $stationId) {
            $station = Station::find($stationId);
            if (!$station) continue;

            // Get annual statistics
            $annualStats = $service->analyzeStationData($stationId, $fromDate, $toDate, $sdType);
            if (!$annualStats) continue;

            // Get monthly statistics
            $monthlyStats = $service->analyzeMonthlyData($stationId, $fromDate, $toDate, $sdType);
            if (empty($monthlyStats)) continue;

            // Get recommended periods
            $recommendations = $service->getRecommendedPeriods($monthlyStats);

            $results[] = [
                'station_id' => $stationId,
                'station_name' => $station->station_name,
                'lat' => round($station->lat ?? 23.8, 2),
                'lon' => round($station->lon ?? 90.4, 2),
                'elev' => round($station->elevation ?? 0, 0),
                'years_analyzed' => $annualStats['years_analyzed'],
                'annual_stats' => $annualStats,
                'monthly_stats' => array_values($monthlyStats),
                'recommendations' => $recommendations,
            ];
        }

        // Calculate overall summary
        $summary = [
            'station_count' => count($results),
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'sd_type' => $sdType,
            'config' => [
                'rainfall_thresholds' => $config['rainfall'],
                'temperature_thresholds' => $config['temperature'],
                'humidity_thresholds' => $config['humidity'],
                'csi_weights' => $config['csi_weights'],
                'csi_ratings' => $config['csi_ratings'],
                'seasons' => $config['seasons'],
                'season_names' => $config['season_names'],
                'work_types' => $config['work_types'],
            ],
        ];

        if (!empty($results)) {
            // Aggregate overall best periods across all stations
            $allExcellent = [];
            $allGood = [];
            $allAvoid = [];

            foreach ($results as $r) {
                $allExcellent = array_merge($allExcellent, $r['recommendations']['excellent']);
                $allGood = array_merge($allGood, $r['recommendations']['good']);
                $allAvoid = array_merge($allAvoid, $r['recommendations']['avoid']);
            }

            // Count occurrences to find most common recommendations
            $excellentCounts = array_count_values($allExcellent);
            $goodCounts = array_count_values($allGood);
            $avoidCounts = array_count_values($allAvoid);

            arsort($excellentCounts);
            arsort($goodCounts);
            arsort($avoidCounts);

            $summary['overall_excellent_months'] = array_keys(array_slice($excellentCounts, 0, 4, true));
            $summary['overall_good_months'] = array_keys(array_slice($goodCounts, 0, 4, true));
            $summary['overall_avoid_months'] = array_keys(array_slice($avoidCounts, 0, 4, true));

            // Calculate overall averages
            $allAnnualRainfall = array_column(array_column($results, 'annual_stats'), 'annual_rainfall');
            $allDryDays = array_column(array_column($results, 'annual_stats'), 'dry_days');
            $allWorkingDays = array_column(array_column($results, 'annual_stats'), 'working_days');

            if (!empty($allAnnualRainfall)) {
                $rainfallMeans = array_column($allAnnualRainfall, 'mean');
                $summary['avg_annual_rainfall'] = round(array_sum($rainfallMeans) / count($rainfallMeans), 0);
            }
            if (!empty($allDryDays)) {
                $dryDayMeans = array_column($allDryDays, 'mean');
                $summary['avg_dry_days'] = round(array_sum($dryDayMeans) / count($dryDayMeans), 0);
            }
            if (!empty($allWorkingDays)) {
                $workingDayMeans = array_column($allWorkingDays, 'mean');
                $summary['avg_working_days'] = round(array_sum($workingDayMeans) / count($workingDayMeans), 0);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'summary' => $summary,
        ]);
    }
}
