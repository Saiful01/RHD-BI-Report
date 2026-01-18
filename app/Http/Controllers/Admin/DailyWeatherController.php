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
}
