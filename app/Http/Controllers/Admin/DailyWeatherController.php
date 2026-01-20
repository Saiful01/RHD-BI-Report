<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyDailyWeatherRequest;
use App\Http\Requests\StoreDailyWeatherRequest;
use App\Http\Requests\UpdateDailyWeatherRequest;
use App\Models\DailyWeather;
use App\Models\Station;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class DailyWeatherController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = DailyWeather::with(['station'])->select('daily_weathers.*');

            if (!empty($request->station_id)) {
                $query->where('station_id', $request->station_id);
            }
            if (!empty($request->from_date) && !empty($request->to_date)) {
                $query->whereBetween('record_date', [$request->from_date, $request->to_date]);
            }

            $perPage = $request->get('per_page', 12);
            $results = $query->orderBy('record_date', 'desc')->paginate($perPage);

            // Get stats
            $totalRecords = DailyWeather::count();
            $stationCount = Station::count();

            // Properly serialize items with relationships
            $items = collect($results->items())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'station_id' => $item->station_id,
                    'max_temp' => $item->max_temp,
                    'mini_temp' => $item->mini_temp,
                    'avg_temp' => $item->avg_temp,
                    'humidity' => $item->humidity,
                    'dry_bulb' => $item->dry_bulb,
                    'dew_point' => $item->dew_point,
                    'total_rain_fall' => $item->total_rain_fall,
                    'total_sunshine_hour' => $item->total_sunshine_hour,
                    'record_date' => $item->record_date,
                    'station' => $item->station ? [
                        'id' => $item->station->id,
                        'station_name' => $item->station->station_name
                    ] : null
                ];
            });

            return response()->json([
                'data' => $items,
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem(),
                'total' => $results->total(),
                'stats' => [
                    'total_records' => $totalRecords,
                    'station_count' => $stationCount
                ]
            ]);
        }

        $stations = Station::pluck('station_name', 'id');
        return view('admin.dailyWeathers.index', compact('stations'));
    }

    public function create()
    {
        abort_if(Gate::denies('daily_weather_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Redirect to index since we use modals now
        return redirect()->route('admin.daily-weathers.index');
    }

    public function store(StoreDailyWeatherRequest $request)
    {
        $data = $request->all();

        // Handle date format conversion
        if (!empty($data['record_date'])) {
            $data['record_date'] = Carbon::parse($data['record_date'])->format(config('panel.date_format'));
        }

        $dailyWeather = DailyWeather::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Weather record created successfully',
                'weather' => $dailyWeather->load('station')
            ]);
        }

        return redirect()->route('admin.daily-weathers.index');
    }

    public function edit(DailyWeather $dailyWeather)
    {
        abort_if(Gate::denies('daily_weather_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Redirect to index since we use modals now
        return redirect()->route('admin.daily-weathers.index');
    }

    public function update(UpdateDailyWeatherRequest $request, DailyWeather $dailyWeather)
    {
        $data = $request->all();

        // Handle date format conversion
        if (!empty($data['record_date'])) {
            $data['record_date'] = Carbon::parse($data['record_date'])->format(config('panel.date_format'));
        }

        $dailyWeather->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Weather record updated successfully',
                'weather' => $dailyWeather->load('station')
            ]);
        }

        return redirect()->route('admin.daily-weathers.index');
    }

    public function show(DailyWeather $dailyWeather, Request $request)
    {
        abort_if(Gate::denies('daily_weather_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dailyWeather->load('station');

        if ($request->ajax() || $request->wantsJson()) {
            // Get raw date for form input
            $rawDate = $dailyWeather->getRawOriginal('record_date');

            return response()->json([
                'success' => true,
                'weather' => array_merge($dailyWeather->toArray(), [
                    'record_date_raw' => $rawDate ? Carbon::parse($rawDate)->format('Y-m-d') : null
                ])
            ]);
        }

        // Redirect to index since we use modals now
        return redirect()->route('admin.daily-weathers.index');
    }

    public function destroy(DailyWeather $dailyWeather, Request $request)
    {
        abort_if(Gate::denies('daily_weather_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dailyWeather->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Weather record deleted successfully'
            ]);
        }

        return back();
    }

    public function massDestroy(MassDestroyDailyWeatherRequest $request)
    {
        $dailyWeathers = DailyWeather::find(request('ids'));

        foreach ($dailyWeathers as $dailyWeather) {
            $dailyWeather->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get dashboard overview data
     */
    public function dashboardData(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get latest available date (use raw DB query to avoid accessor)
        $latestDate = DB::table('daily_weathers')->whereNull('deleted_at')->max('record_date');
        $latestDateFormatted = $latestDate ? Carbon::parse($latestDate)->format('Y-m-d') : null;

        // Get latest weather data for all stations
        $latestData = DailyWeather::with('station')
            ->whereDate('record_date', $latestDate)
            ->get();

        // Calculate overview stats
        $overviewStats = [
            'avg_max_temp' => round($latestData->avg('max_temp'), 1),
            'avg_min_temp' => round($latestData->avg('mini_temp'), 1),
            'total_rainfall' => round($latestData->sum('total_rain_fall'), 1),
            'avg_humidity' => round($latestData->avg('humidity'), 1),
            'total_sunshine' => round($latestData->sum('total_sunshine_hour'), 1),
            'stations_reporting' => $latestData->count(),
            'latest_date' => $latestDateFormatted,
        ];

        // Hottest station
        $hottestStation = $latestData->sortByDesc('max_temp')->first();
        $overviewStats['hottest'] = $hottestStation ? [
            'station' => $hottestStation->station->station_name ?? 'Unknown',
            'temp' => $hottestStation->max_temp,
        ] : null;

        // Coldest station
        $coldestStation = $latestData->sortBy('mini_temp')->first();
        $overviewStats['coldest'] = $coldestStation ? [
            'station' => $coldestStation->station->station_name ?? 'Unknown',
            'temp' => $coldestStation->mini_temp,
        ] : null;

        // Wettest station
        $wettestStation = $latestData->where('total_rain_fall', '>', 0)->sortByDesc('total_rain_fall')->first();
        $overviewStats['wettest'] = $wettestStation ? [
            'station' => $wettestStation->station->station_name ?? 'Unknown',
            'rainfall' => $wettestStation->total_rain_fall,
        ] : null;

        // Station summaries for cards
        $stationSummaries = $latestData->map(function ($item) {
            return [
                'id' => $item->station_id,
                'station_name' => $item->station->station_name ?? 'Unknown',
                'max_temp' => $item->max_temp,
                'min_temp' => $item->mini_temp,
                'humidity' => $item->humidity,
                'rainfall' => $item->total_rain_fall,
                'sunshine' => $item->total_sunshine_hour,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'overview' => $overviewStats,
            'stations' => $stationSummaries,
        ]);
    }

    /**
     * Get calendar heatmap data
     */
    public function calendarData(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stationId = $request->get('station_id');
        $requestedYear = $request->get('year', date('Y'));
        $metric = $request->get('metric', 'max_temp'); // max_temp, rainfall, humidity

        // Get available years first
        $availableYearsQuery = DB::table('daily_weathers')->whereNull('deleted_at');
        if ($stationId) {
            $availableYearsQuery->where('station_id', $stationId);
        }
        $availableYears = $availableYearsQuery
            ->selectRaw('YEAR(record_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Use requested year if available, otherwise use latest available year
        $year = in_array((int)$requestedYear, $availableYears) ? $requestedYear : ($availableYears[0] ?? date('Y'));

        $query = DB::table('daily_weathers')
            ->whereNull('deleted_at')
            ->whereYear('record_date', $year);

        if ($stationId) {
            $query->where('station_id', $stationId);
        }

        $data = $query->selectRaw('
                record_date,
                ROUND(AVG(max_temp), 1) as max_temp,
                ROUND(AVG(mini_temp), 1) as min_temp,
                ROUND(SUM(total_rain_fall), 1) as rainfall,
                ROUND(AVG(humidity), 1) as humidity
            ')
            ->groupBy('record_date')
            ->orderBy('record_date')
            ->get()
            ->map(function ($item) use ($metric) {
                $value = 0;
                switch ($metric) {
                    case 'max_temp':
                        $value = $item->max_temp;
                        break;
                    case 'min_temp':
                        $value = $item->min_temp;
                        break;
                    case 'rainfall':
                        $value = $item->rainfall;
                        break;
                    case 'humidity':
                        $value = $item->humidity;
                        break;
                }
                return [
                    'date' => Carbon::parse($item->record_date)->format('Y-m-d'),
                    'value' => $value,
                    'max_temp' => $item->max_temp,
                    'min_temp' => $item->min_temp,
                    'rainfall' => $item->rainfall,
                    'humidity' => $item->humidity,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'year' => $year,
            'metric' => $metric,
            'available_years' => $availableYears,
        ]);
    }

    /**
     * Get comparison data for multiple stations
     */
    public function comparisonData(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Handle both array formats: stations[]=1&stations[]=2 and stations=1,2,3
        $stationIds = $request->get('stations', []);
        if (is_string($stationIds)) {
            $stationIds = array_filter(explode(',', $stationIds));
        }
        if (!is_array($stationIds)) {
            $stationIds = [];
        }

        $period = $request->get('period', 'month'); // month, year
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        if (empty($stationIds)) {
            // Get first 4 stations by default
            $stationIds = Station::take(4)->pluck('id')->toArray();
        }

        $stations = Station::whereIn('id', $stationIds)->get();

        $comparisonData = [];

        foreach ($stations as $station) {
            $query = DB::table('daily_weathers')
                ->whereNull('deleted_at')
                ->where('station_id', $station->id)
                ->whereYear('record_date', $year);

            if ($period === 'month') {
                $query->whereMonth('record_date', $month);
            }

            $data = $query->selectRaw('
                    ROUND(AVG(max_temp), 1) as avg_max_temp,
                    ROUND(AVG(mini_temp), 1) as avg_min_temp,
                    ROUND(AVG(humidity), 1) as avg_humidity,
                    ROUND(SUM(total_rain_fall), 1) as total_rainfall,
                    ROUND(SUM(total_sunshine_hour), 1) as total_sunshine,
                    ROUND(MAX(max_temp), 1) as peak_temp,
                    ROUND(MIN(mini_temp), 1) as lowest_temp,
                    COUNT(*) as days_recorded
                ')
                ->first();

            $comparisonData[] = [
                'station_id' => $station->id,
                'station_name' => $station->station_name,
                'avg_max_temp' => $data->avg_max_temp ?? 0,
                'avg_min_temp' => $data->avg_min_temp ?? 0,
                'avg_humidity' => $data->avg_humidity ?? 0,
                'total_rainfall' => $data->total_rainfall ?? 0,
                'total_sunshine' => $data->total_sunshine ?? 0,
                'peak_temp' => $data->peak_temp ?? 0,
                'lowest_temp' => $data->lowest_temp ?? 0,
                'days_recorded' => $data->days_recorded ?? 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $comparisonData,
            'period' => $period,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * Get records and extremes
     */
    public function recordsData(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stationId = $request->get('station_id');

        $query = DailyWeather::with('station');
        if ($stationId) {
            $query->where('station_id', $stationId);
        }

        // Hottest day ever
        $hottestDay = (clone $query)->orderByDesc('max_temp')->first();

        // Coldest day ever
        $coldestDay = (clone $query)->orderBy('mini_temp')->first();

        // Wettest day ever
        $wettestDay = (clone $query)->orderByDesc('total_rain_fall')->first();

        // Most sunshine
        $sunniestDay = (clone $query)->orderByDesc('total_sunshine_hour')->first();

        // Highest humidity
        $humidestDay = (clone $query)->orderByDesc('humidity')->first();

        // Monthly records
        $monthlyRecords = DailyWeather::when($stationId, fn($q) => $q->where('station_id', $stationId))
            ->selectRaw('
                MONTH(record_date) as month,
                ROUND(MAX(max_temp), 1) as max_temp_record,
                ROUND(MIN(mini_temp), 1) as min_temp_record,
                ROUND(MAX(total_rain_fall), 1) as max_rainfall,
                ROUND(AVG(max_temp), 1) as avg_max_temp,
                ROUND(AVG(mini_temp), 1) as avg_min_temp
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                return [
                    'month' => $monthNames[$item->month],
                    'month_num' => $item->month,
                    'max_temp_record' => $item->max_temp_record,
                    'min_temp_record' => $item->min_temp_record,
                    'max_rainfall' => $item->max_rainfall,
                    'avg_max_temp' => $item->avg_max_temp,
                    'avg_min_temp' => $item->avg_min_temp,
                ];
            });

        // Yearly trends
        $yearlyTrends = DailyWeather::when($stationId, fn($q) => $q->where('station_id', $stationId))
            ->selectRaw('
                YEAR(record_date) as year,
                ROUND(AVG(max_temp), 2) as avg_max_temp,
                ROUND(AVG(mini_temp), 2) as avg_min_temp,
                ROUND(SUM(total_rain_fall), 1) as total_rainfall,
                COUNT(*) as days_recorded
            ')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        return response()->json([
            'success' => true,
            'extremes' => [
                'hottest' => $hottestDay ? [
                    'date' => $hottestDay->record_date,
                    'station' => $hottestDay->station->station_name ?? 'Unknown',
                    'value' => $hottestDay->max_temp,
                ] : null,
                'coldest' => $coldestDay ? [
                    'date' => $coldestDay->record_date,
                    'station' => $coldestDay->station->station_name ?? 'Unknown',
                    'value' => $coldestDay->mini_temp,
                ] : null,
                'wettest' => $wettestDay ? [
                    'date' => $wettestDay->record_date,
                    'station' => $wettestDay->station->station_name ?? 'Unknown',
                    'value' => $wettestDay->total_rain_fall,
                ] : null,
                'sunniest' => $sunniestDay ? [
                    'date' => $sunniestDay->record_date,
                    'station' => $sunniestDay->station->station_name ?? 'Unknown',
                    'value' => $sunniestDay->total_sunshine_hour,
                ] : null,
                'humidest' => $humidestDay ? [
                    'date' => $humidestDay->record_date,
                    'station' => $humidestDay->station->station_name ?? 'Unknown',
                    'value' => $humidestDay->humidity,
                ] : null,
            ],
            'monthly_records' => $monthlyRecords,
            'yearly_trends' => $yearlyTrends,
        ]);
    }

    /**
     * Get weekly trend data
     */
    public function weeklyTrend(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stationId = $request->get('station_id');
        $endDateRaw = DB::table('daily_weathers')->whereNull('deleted_at')->max('record_date');
        $endDate = $endDateRaw;
        $startDate = Carbon::parse($endDateRaw)->subDays(6)->format('Y-m-d');

        $query = DB::table('daily_weathers')
            ->whereNull('deleted_at')
            ->whereBetween('record_date', [$startDate, $endDate]);

        if ($stationId) {
            $query->where('station_id', $stationId);
        }

        $data = $query->selectRaw('
                record_date,
                ROUND(AVG(max_temp), 1) as max_temp,
                ROUND(AVG(mini_temp), 1) as min_temp,
                ROUND(SUM(total_rain_fall), 1) as rainfall,
                ROUND(AVG(humidity), 1) as humidity,
                ROUND(SUM(total_sunshine_hour), 1) as sunshine
            ')
            ->groupBy('record_date')
            ->orderBy('record_date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->record_date)->format('D'),
                    'full_date' => Carbon::parse($item->record_date)->format('M d'),
                    'max_temp' => $item->max_temp,
                    'min_temp' => $item->min_temp,
                    'rainfall' => $item->rainfall,
                    'humidity' => $item->humidity,
                    'sunshine' => $item->sunshine,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Generate weather analysis report with statistical data
     */
    public function weatherReportData(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stationIds = $request->get('stations', []);
        if (is_string($stationIds)) {
            $stationIds = array_filter(explode(',', $stationIds));
        }

        $fromDate = $request->get('from_date', '2020-01-01');
        $toDate = $request->get('to_date', date('Y-m-d'));
        $maxCount = (int) $request->get('max_count', 10);
        $minCount = (int) $request->get('min_count', 10);
        $sdType = $request->get('sd_type', 'population'); // 'population' or 'sample'

        $results = [];
        $overallHighMax = null;
        $overallLowMin = null;

        foreach ($stationIds as $stationId) {
            $station = Station::find($stationId);
            if (!$station) continue;

            // Get weather data for this station in date range
            $weatherData = DB::table('daily_weathers')
                ->where('station_id', $stationId)
                ->whereBetween('record_date', [$fromDate, $toDate])
                ->whereNull('deleted_at')
                ->get();

            if ($weatherData->isEmpty()) continue;

            // Get top N highest max temps
            $highTemps = $weatherData->whereNotNull('max_temp')
                ->sortByDesc('max_temp')
                ->take($maxCount)
                ->pluck('max_temp')
                ->values();

            // Get top N lowest min temps
            $lowTemps = $weatherData->whereNotNull('mini_temp')
                ->sortBy('mini_temp')
                ->take($minCount)
                ->pluck('mini_temp')
                ->values();

            if ($highTemps->isEmpty() || $lowTemps->isEmpty()) continue;

            // Calculate statistics
            $highAvg = round($highTemps->avg(), 2);
            $lowAvg = round($lowTemps->avg(), 2);

            // Standard deviation calculation
            $highStd = $this->calculateStdDev($highTemps->toArray(), $sdType);
            $lowStd = $this->calculateStdDev($lowTemps->toArray(), $sdType);

            // 50% Reliability (using extreme values)
            $maxAir50 = round($highTemps->max(), 2);
            $minAir50 = round($lowTemps->min(), 2);

            // 98% Reliability (applying ±2 standard deviations)
            $maxAir98 = round($maxAir50 + (2 * $highStd), 2);
            $minAir98 = round($minAir50 - (2 * $lowStd), 2);

            // Calculate PVT values using latitude
            $lat = $station->lat ?? 23.8; // Default to Bangladesh latitude
            $pvtMax50 = $this->calculatePVT($maxAir50, $lat);
            $pvtMin50 = $this->calculatePVT($minAir50, $lat);
            $pvtMax98 = $this->calculatePVT($maxAir98, $lat);
            $pvtMin98 = $this->calculatePVT($minAir98, $lat);

            $results[] = [
                'station_id' => $stationId,
                'station_name' => $station->station_name,
                'high_avg' => $highAvg,
                'high_std' => round($highStd, 2),
                'low_avg' => $lowAvg,
                'low_std' => round($lowStd, 2),
                'max_air_50' => $maxAir50,
                'pvt_max_50' => $pvtMax50,
                'min_air_50' => $minAir50,
                'pvt_min_50' => $pvtMin50,
                'max_air_98' => $maxAir98,
                'pvt_max_98' => $pvtMax98,
                'min_air_98' => $minAir98,
                'pvt_min_98' => $pvtMin98,
            ];

            // Track overall extremes
            if ($overallHighMax === null || $maxAir98 > $overallHighMax) {
                $overallHighMax = $maxAir98;
            }
            if ($overallLowMin === null || $minAir98 < $overallLowMin) {
                $overallLowMin = $minAir98;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'summary' => [
                'station_count' => count($results),
                'overall_high_max' => $overallHighMax,
                'overall_low_min' => $overallLowMin,
                'temp_range' => $overallHighMax !== null && $overallLowMin !== null
                    ? round($overallHighMax - $overallLowMin, 2)
                    : null,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
        ]);
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStdDev(array $values, string $type = 'population'): float
    {
        $count = count($values);
        if ($count < 2) return 0;

        $mean = array_sum($values) / $count;
        $sumSquaredDiff = 0;

        foreach ($values as $value) {
            $sumSquaredDiff += pow($value - $mean, 2);
        }

        // Population SD uses N, Sample SD uses N-1
        $divisor = $type === 'sample' ? ($count - 1) : $count;

        return sqrt($sumSquaredDiff / $divisor);
    }

    /**
     * Calculate PVT (Pressure Vapor Temperature)
     * Formula: (Ta - 0.00618 × Lat² + 0.2289 × Lat + 42.2) × 0.9545 - 17.78
     */
    private function calculatePVT(float $airTemp, float $latitude): float
    {
        $pvt = ($airTemp - 0.00618 * pow($latitude, 2) + 0.2289 * $latitude + 42.2) * 0.9545 - 17.78;
        return round($pvt, 2);
    }

    /**
     * Pavement Temperature Analysis using SUPERPAVE methodology
     * Based on SHRP-A-648A formulas
     */
    public function pavementAnalysisData(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get parameters
        $stationIds = $request->get('stations', []);
        if (is_string($stationIds)) {
            $stationIds = array_filter(explode(',', $stationIds));
        }

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $hotDays = (int) $request->get('hot_days', 7); // Number of hottest days to consider
        $coldDays = (int) $request->get('cold_days', 1); // Number of coldest days to consider
        $sdType = $request->get('sd_type', 'population'); // 'population' or 'sample'

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

        foreach ($stationIds as $stationId) {
            $station = Station::find($stationId);
            if (!$station) continue;

            // Get weather data for this station in date range
            $weatherData = DB::table('daily_weathers')
                ->where('station_id', $stationId)
                ->whereBetween('record_date', [$fromDate, $toDate])
                ->whereNull('deleted_at')
                ->get();

            if ($weatherData->isEmpty()) continue;

            // SUPERPAVE Methodology:
            // For each YEAR, get the coldest N days and hottest N-day average
            // Then calculate AVG and STD from those yearly values

            $years = $weatherData->groupBy(function($item) {
                return Carbon::parse($item->record_date)->year;
            });

            $yearlyHighAvgs = []; // Average of hottest N days per year
            $yearlyLowAvgs = [];  // Average of coldest N days per year

            foreach ($years as $year => $yearData) {
                // Get top N highest max temps for this year
                $yearHighTemps = collect($yearData)->whereNotNull('max_temp')
                    ->sortByDesc('max_temp')
                    ->take($hotDays)
                    ->pluck('max_temp')
                    ->values();

                if ($yearHighTemps->count() >= $hotDays) {
                    $yearlyHighAvgs[] = $yearHighTemps->avg();
                }

                // Get top N lowest min temps for this year
                $yearLowTemps = collect($yearData)->whereNotNull('mini_temp')
                    ->sortBy('mini_temp')
                    ->take($coldDays)
                    ->pluck('mini_temp')
                    ->values();

                if ($yearLowTemps->count() >= $coldDays) {
                    $yearlyLowAvgs[] = $yearLowTemps->avg();
                }
            }

            if (empty($yearlyHighAvgs) || empty($yearlyLowAvgs)) continue;

            // Calculate AVG HIGH and AVG LOW from yearly values
            $avgHigh = round(array_sum($yearlyHighAvgs) / count($yearlyHighAvgs), 2);
            $avgLow = round(array_sum($yearlyLowAvgs) / count($yearlyLowAvgs), 2);

            // Calculate Standard Deviations from yearly values
            $stdHigh = $this->calculateStdDev($yearlyHighAvgs, $sdType);
            $stdLow = $this->calculateStdDev($yearlyLowAvgs, $sdType);

            // Get station coordinates
            $lat = $station->lat ?? 23.8; // Default to Bangladesh latitude if null
            $lon = $station->lon ?? 90.4;
            $elev = $station->elevation ?? 0;

            // 50% Reliability calculations
            $maxAir50 = $avgHigh;
            $minAir50 = $avgLow;
            $maxPvt50 = $this->calculateSuperpavePVT($maxAir50, $lat);
            $minPvt50 = $minAir50; // MIN PVT equals MIN AIR

            // If MAX PVT is less than 0, use MIN AIR instead
            if ($maxPvt50 < 0) {
                $maxPvt50 = $minAir50;
            }

            // 98% Reliability calculations (using 2.055 standard deviations)
            $maxAir98 = round($avgHigh + (2.055 * $stdHigh), 2);
            $minAir98 = round($avgLow - (2.055 * $stdLow), 2);
            $maxPvt98 = $this->calculateSuperpavePVT($maxAir98, $lat);
            $minPvt98 = $minAir98; // MIN PVT equals MIN AIR

            // If MAX PVT is less than 0, use MIN AIR instead
            if ($maxPvt98 < 0) {
                $maxPvt98 = $minAir98;
            }

            // Prepare yearly data for graphing
            $yearlyData = [];
            foreach ($years as $year => $yearData) {
                $yearHighTemps = collect($yearData)->whereNotNull('max_temp')
                    ->sortByDesc('max_temp')
                    ->take($hotDays)
                    ->pluck('max_temp')
                    ->values();

                $yearLowTemps = collect($yearData)->whereNotNull('mini_temp')
                    ->sortBy('mini_temp')
                    ->take($coldDays)
                    ->pluck('mini_temp')
                    ->values();

                if ($yearHighTemps->count() >= $hotDays && $yearLowTemps->count() >= $coldDays) {
                    $yearlyData[] = [
                        'year' => $year,
                        'high_avg' => round($yearHighTemps->avg(), 2),
                        'low_avg' => round($yearLowTemps->avg(), 2),
                    ];
                }
            }

            // Sort yearly data by year
            usort($yearlyData, function($a, $b) {
                return $a['year'] - $b['year'];
            });

            $results[] = [
                'station_id' => $stationId,
                'station_name' => $station->station_name,
                'lon' => round($lon, 2),
                'lat' => round($lat, 2),
                'elev' => round($elev, 0),
                'avg_low' => round($avgLow, 1),
                'std_low' => round($stdLow, 1),
                'avg_high' => round($avgHigh, 1),
                'std_high' => round($stdHigh, 1),
                // 50% Reliability
                'max_air_50' => round($maxAir50, 0),
                'max_pvt_50' => round($maxPvt50, 0),
                'min_air_50' => round($minAir50, 0),
                'min_pvt_50' => round($minPvt50, 0),
                // 98% Reliability
                'max_air_98' => round($maxAir98, 0),
                'max_pvt_98' => round($maxPvt98, 0),
                'min_air_98' => round($minAir98, 0),
                'min_pvt_98' => round($minPvt98, 0),
                // Yearly data for graphing
                'yearly_data' => $yearlyData,
            ];
        }

        // Calculate summary statistics
        $summary = [
            'station_count' => count($results),
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'hot_days' => $hotDays,
            'cold_days' => $coldDays,
            'sd_type' => $sdType,
        ];

        if (!empty($results)) {
            $summary['overall_max_air'] = max(array_column($results, 'max_air_98'));
            $summary['overall_min_air'] = min(array_column($results, 'min_air_98'));
            $summary['overall_max_pvt'] = max(array_column($results, 'max_pvt_98'));
            $summary['overall_min_pvt'] = min(array_column($results, 'min_pvt_98'));
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'summary' => $summary,
        ]);
    }

    /**
     * Calculate Pavement Temperature at 20mm depth using SUPERPAVE formula
     * Formula: T_20mm = (T_air - 0.00618 × lat² + 0.2289 × lat + 42.2) × 0.9545 - 17.78
     * Source: SHRP-A-648A
     */
    private function calculateSuperpavePVT(float $airTemp, float $latitude): float
    {
        $pvt = ($airTemp - 0.00618 * pow($latitude, 2) + 0.2289 * $latitude + 42.2) * 0.9545 - 17.78;
        return round($pvt, 2);
    }

    /**
     * Construction Weather Analysis - Bangladesh specific
     * Calculates construction suitability based on rainfall, temperature, humidity, sunshine
     * Uses multi-year statistical analysis with 50%/98% reliability levels
     */
    public function constructionAnalysisData(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $service = new \App\Services\ConstructionWeatherService();

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
