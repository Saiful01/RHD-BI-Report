<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyStationRequest;
use App\Http\Requests\StoreStationRequest;
use App\Http\Requests\UpdateStationRequest;
use App\Models\Station;
use App\Models\DailyWeather;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class StationController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('station_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = Station::query();

            if ($request->filled('search')) {
                $query->where('station_name', 'LIKE', '%' . $request->search . '%');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Get counts for stats
            $totalCount = Station::count();
            $activeCount = Station::where('status', '1')->count();
            $inactiveCount = Station::where('status', '0')->count();

            $perPage = $request->get('per_page', 12);
            $stations = $query->orderBy('station_name', 'asc')->paginate($perPage);

            return response()->json([
                'data' => $stations->items(),
                'current_page' => $stations->currentPage(),
                'last_page' => $stations->lastPage(),
                'from' => $stations->firstItem(),
                'to' => $stations->lastItem(),
                'total' => $stations->total(),
                'stats' => [
                    'total' => $totalCount,
                    'active' => $activeCount,
                    'inactive' => $inactiveCount
                ]
            ]);
        }

        return view('admin.stations.index');
    }

    public function create()
    {
        abort_if(Gate::denies('station_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Redirect to index since we use modals now
        return redirect()->route('admin.stations.index');
    }

    public function store(StoreStationRequest $request)
    {
        $station = Station::create($request->all());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Station created successfully',
                'station' => $station
            ]);
        }

        return redirect()->route('admin.stations.index');
    }

    public function edit(Station $station)
    {
        abort_if(Gate::denies('station_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Redirect to index since we use modals now
        return redirect()->route('admin.stations.index');
    }

    public function update(UpdateStationRequest $request, Station $station)
    {
        $station->update($request->all());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Station updated successfully',
                'station' => $station
            ]);
        }

        return redirect()->route('admin.stations.index');
    }

    public function show(Station $station, Request $request)
    {
        abort_if(Gate::denies('station_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'station' => $station
            ]);
        }

        // Redirect to index since we use modals now
        return redirect()->route('admin.stations.index');
    }

    public function destroy(Station $station)
    {
        abort_if(Gate::denies('station_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $station->delete();

        return back();
    }

    public function massDestroy(MassDestroyStationRequest $request)
    {
        $stations = Station::find(request('ids'));

        foreach ($stations as $station) {
            $station->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function analytics(Station $station)
    {
        abort_if(Gate::denies('station_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get date range for this station
        $dateRange = DailyWeather::where('station_id', $station->id)
            ->selectRaw('MIN(record_date) as min_date, MAX(record_date) as max_date')
            ->first();

        // Get available years for this station
        $availableYears = DailyWeather::where('station_id', $station->id)
            ->selectRaw('YEAR(record_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        return view('admin.stations.analytics', compact('station', 'dateRange', 'availableYears'));
    }

    public function analyticsData(Station $station, Request $request)
    {
        abort_if(Gate::denies('station_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $viewType = $request->get('view', 'month'); // month, year, decade
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));
        $decadeStart = $request->get('decade_start', date('Y') - 9);

        $data = [];

        if ($viewType === 'month') {
            // Daily data for a specific month
            $data = DailyWeather::where('station_id', $station->id)
                ->whereYear('record_date', $year)
                ->whereMonth('record_date', $month)
                ->orderBy('record_date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->getRawOriginal('record_date'))->format('d'),
                        'full_date' => Carbon::parse($item->getRawOriginal('record_date'))->format('M d, Y'),
                        'max_temp' => (float) $item->max_temp,
                        'min_temp' => (float) $item->mini_temp,
                        'avg_temp' => (float) $item->avg_temp,
                        'humidity' => (float) $item->humidity,
                        'rainfall' => (float) $item->total_rain_fall,
                        'sunshine' => (float) $item->total_sunshine_hour,
                        'dew_point' => (float) $item->dew_point,
                        'dry_bulb' => (float) $item->dry_bulb,
                    ];
                });

            // Calculate summary stats
            $summary = [
                'avg_max_temp' => round($data->avg('max_temp'), 1),
                'avg_min_temp' => round($data->avg('min_temp'), 1),
                'total_rainfall' => round($data->sum('rainfall'), 1),
                'avg_humidity' => round($data->avg('humidity'), 1),
                'total_sunshine' => round($data->sum('sunshine'), 1),
                'rainy_days' => $data->where('rainfall', '>', 0)->count(),
            ];

        } elseif ($viewType === 'year') {
            // Monthly aggregated data for a specific year
            $data = DailyWeather::where('station_id', $station->id)
                ->whereYear('record_date', $year)
                ->selectRaw('
                    MONTH(record_date) as month,
                    ROUND(AVG(max_temp), 2) as avg_max_temp,
                    ROUND(AVG(mini_temp), 2) as avg_min_temp,
                    ROUND(AVG(avg_temp), 2) as avg_temp,
                    ROUND(AVG(humidity), 2) as avg_humidity,
                    ROUND(SUM(total_rain_fall), 2) as total_rainfall,
                    ROUND(SUM(total_sunshine_hour), 2) as total_sunshine,
                    ROUND(AVG(dew_point), 2) as avg_dew_point,
                    ROUND(MAX(max_temp), 2) as max_temp_peak,
                    ROUND(MIN(mini_temp), 2) as min_temp_low,
                    COUNT(CASE WHEN total_rain_fall > 0 THEN 1 END) as rainy_days
                ')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {
                    $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    return [
                        'month' => $monthNames[$item->month],
                        'month_num' => $item->month,
                        'avg_max_temp' => (float) $item->avg_max_temp,
                        'avg_min_temp' => (float) $item->avg_min_temp,
                        'avg_temp' => (float) $item->avg_temp,
                        'avg_humidity' => (float) $item->avg_humidity,
                        'total_rainfall' => (float) $item->total_rainfall,
                        'total_sunshine' => (float) $item->total_sunshine,
                        'avg_dew_point' => (float) $item->avg_dew_point,
                        'max_temp_peak' => (float) $item->max_temp_peak,
                        'min_temp_low' => (float) $item->min_temp_low,
                        'rainy_days' => (int) $item->rainy_days,
                    ];
                });

            $summary = [
                'avg_max_temp' => round($data->avg('avg_max_temp'), 1),
                'avg_min_temp' => round($data->avg('avg_min_temp'), 1),
                'total_rainfall' => round($data->sum('total_rainfall'), 1),
                'avg_humidity' => round($data->avg('avg_humidity'), 1),
                'total_sunshine' => round($data->sum('total_sunshine'), 1),
                'rainy_days' => $data->sum('rainy_days'),
                'hottest_month' => $data->sortByDesc('max_temp_peak')->first()['month'] ?? 'N/A',
                'coldest_month' => $data->sortBy('min_temp_low')->first()['month'] ?? 'N/A',
                'wettest_month' => $data->sortByDesc('total_rainfall')->first()['month'] ?? 'N/A',
            ];

        } else {
            // Decade view - Yearly aggregated data
            $decadeEnd = $decadeStart + 9;
            $data = DailyWeather::where('station_id', $station->id)
                ->whereYear('record_date', '>=', $decadeStart)
                ->whereYear('record_date', '<=', $decadeEnd)
                ->selectRaw('
                    YEAR(record_date) as year,
                    ROUND(AVG(max_temp), 2) as avg_max_temp,
                    ROUND(AVG(mini_temp), 2) as avg_min_temp,
                    ROUND(AVG(avg_temp), 2) as avg_temp,
                    ROUND(AVG(humidity), 2) as avg_humidity,
                    ROUND(SUM(total_rain_fall), 2) as total_rainfall,
                    ROUND(SUM(total_sunshine_hour), 2) as total_sunshine,
                    ROUND(AVG(dew_point), 2) as avg_dew_point,
                    ROUND(MAX(max_temp), 2) as max_temp_peak,
                    ROUND(MIN(mini_temp), 2) as min_temp_low,
                    COUNT(CASE WHEN total_rain_fall > 0 THEN 1 END) as rainy_days
                ')
                ->groupBy('year')
                ->orderBy('year')
                ->get()
                ->map(function ($item) {
                    return [
                        'year' => $item->year,
                        'avg_max_temp' => (float) $item->avg_max_temp,
                        'avg_min_temp' => (float) $item->avg_min_temp,
                        'avg_temp' => (float) $item->avg_temp,
                        'avg_humidity' => (float) $item->avg_humidity,
                        'total_rainfall' => (float) $item->total_rainfall,
                        'total_sunshine' => (float) $item->total_sunshine,
                        'avg_dew_point' => (float) $item->avg_dew_point,
                        'max_temp_peak' => (float) $item->max_temp_peak,
                        'min_temp_low' => (float) $item->min_temp_low,
                        'rainy_days' => (int) $item->rainy_days,
                    ];
                });

            // Temperature trend analysis
            $firstHalf = $data->take(5);
            $secondHalf = $data->skip(5);
            $tempTrend = 0;
            if ($firstHalf->count() > 0 && $secondHalf->count() > 0) {
                $tempTrend = round($secondHalf->avg('avg_max_temp') - $firstHalf->avg('avg_max_temp'), 2);
            }

            $summary = [
                'avg_max_temp' => round($data->avg('avg_max_temp'), 1),
                'avg_min_temp' => round($data->avg('avg_min_temp'), 1),
                'total_rainfall' => round($data->sum('total_rainfall'), 1),
                'avg_humidity' => round($data->avg('avg_humidity'), 1),
                'rainy_days' => $data->sum('rainy_days'),
                'hottest_year' => $data->sortByDesc('max_temp_peak')->first()['year'] ?? 'N/A',
                'coldest_year' => $data->sortBy('min_temp_low')->first()['year'] ?? 'N/A',
                'wettest_year' => $data->sortByDesc('total_rainfall')->first()['year'] ?? 'N/A',
                'driest_year' => $data->sortBy('total_rainfall')->first()['year'] ?? 'N/A',
                'temp_trend' => $tempTrend,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'summary' => $summary ?? [],
            'view' => $viewType,
            'params' => [
                'year' => $year,
                'month' => $month,
                'decade_start' => $decadeStart,
            ]
        ]);
    }

    /**
     * Get all stations data for map view
     */
    public function mapData(Request $request)
    {
        $stations = Station::select('id', 'station_name', 'lat', 'lon', 'elevation', 'status')
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->get()
            ->map(function ($station) {
                // Get latest weather data
                $latestWeather = DailyWeather::where('station_id', $station->id)
                    ->orderBy('record_date', 'desc')
                    ->first();

                // Get record count and date range
                $recordStats = DailyWeather::where('station_id', $station->id)
                    ->selectRaw('COUNT(*) as total_records, MIN(record_date) as first_date, MAX(record_date) as last_date')
                    ->first();

                // Get current year stats
                $currentYear = date('Y');
                $yearlyStats = DailyWeather::where('station_id', $station->id)
                    ->whereYear('record_date', $currentYear)
                    ->selectRaw('
                        ROUND(AVG(max_temp), 1) as avg_max_temp,
                        ROUND(AVG(mini_temp), 1) as avg_min_temp,
                        ROUND(AVG(humidity), 1) as avg_humidity,
                        ROUND(SUM(total_rain_fall), 1) as total_rainfall,
                        ROUND(MAX(max_temp), 1) as highest_temp,
                        ROUND(MIN(mini_temp), 1) as lowest_temp,
                        COUNT(*) as days_recorded
                    ')
                    ->first();

                // Get last 7 days trend
                $weekTrend = DailyWeather::where('station_id', $station->id)
                    ->orderBy('record_date', 'desc')
                    ->take(7)
                    ->get(['record_date', 'max_temp', 'mini_temp', 'total_rain_fall'])
                    ->map(function ($day) {
                        return [
                            'date' => Carbon::parse($day->getRawOriginal('record_date'))->format('M d'),
                            'max' => (float) $day->max_temp,
                            'min' => (float) $day->mini_temp,
                            'rain' => (float) $day->total_rain_fall,
                        ];
                    })->reverse()->values();

                return [
                    'id' => $station->id,
                    'name' => $station->station_name,
                    'lat' => (float) $station->lat,
                    'lon' => (float) $station->lon,
                    'elevation' => $station->elevation,
                    'status' => in_array($station->status, ['1', 'active', 1]) ? 'active' : 'inactive',
                    'latest_data' => $latestWeather ? [
                        'date' => Carbon::parse($latestWeather->getRawOriginal('record_date'))->format('M d, Y'),
                        'max_temp' => (float) $latestWeather->max_temp,
                        'min_temp' => (float) $latestWeather->mini_temp,
                        'avg_temp' => (float) $latestWeather->avg_temp,
                        'humidity' => (float) $latestWeather->humidity,
                        'rainfall' => (float) $latestWeather->total_rain_fall,
                        'sunshine' => (float) $latestWeather->total_sunshine_hour,
                        'dew_point' => (float) $latestWeather->dew_point,
                    ] : null,
                    'record_stats' => [
                        'total_records' => (int) ($recordStats->total_records ?? 0),
                        'first_date' => $recordStats->first_date ? Carbon::parse($recordStats->first_date)->format('M Y') : null,
                        'last_date' => $recordStats->last_date ? Carbon::parse($recordStats->last_date)->format('M Y') : null,
                    ],
                    'yearly_stats' => $yearlyStats && $yearlyStats->days_recorded > 0 ? [
                        'year' => $currentYear,
                        'avg_max_temp' => (float) $yearlyStats->avg_max_temp,
                        'avg_min_temp' => (float) $yearlyStats->avg_min_temp,
                        'avg_humidity' => (float) $yearlyStats->avg_humidity,
                        'total_rainfall' => (float) $yearlyStats->total_rainfall,
                        'highest_temp' => (float) $yearlyStats->highest_temp,
                        'lowest_temp' => (float) $yearlyStats->lowest_temp,
                        'days_recorded' => (int) $yearlyStats->days_recorded,
                    ] : null,
                    'week_trend' => $weekTrend,
                ];
            });

        $stats = [
            'total' => $stations->count(),
            'active' => $stations->where('status', 'active')->count(),
            'inactive' => $stations->where('status', 'inactive')->count(),
        ];

        return response()->json([
            'success' => true,
            'stations' => $stations,
            'stats' => $stats
        ]);
    }
}
