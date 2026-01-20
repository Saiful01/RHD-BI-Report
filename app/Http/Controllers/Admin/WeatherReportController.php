<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyWeatherReportRequest;
use App\Http\Requests\StoreWeatherReportRequest;
use App\Http\Requests\UpdateWeatherReportRequest;
use App\Models\DailyWeather;
use App\Models\Station;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WeatherReportController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('weather_report_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stations = Station::pluck('station_name', 'id');
        $reportData = [];
        $trendData = [];
        $stdChartData = [
            'labels' => [],
            'high_std' => [],
            'low_std' => []
        ];

        if (!$request->has('station_ids')) {
            $defaultStationIds = Station::orderBy('id', 'asc')->take(2)->pluck('id')->toArray();

            $request->merge([
                'station_ids'    => $defaultStationIds,
                'from_date'      => '2020-01-01',
                'to_date'        => '2025-01-01',
                'sd_type'        => '1', // Default Population
                'max_avg_value'  => 7,
                'min_avg_value'  => 1,
            ]);
        }

        if ($request->has('station_ids') && !empty($request->station_ids)) {
            $dailyRecords = DailyWeather::whereIn('station_id', $request->station_ids)
                ->whereBetween('record_date', [$request->from_date, $request->to_date])
                ->orderBy('record_date', 'ASC')
                ->with('station')
                ->get();

            $groupedData = $dailyRecords->groupBy('station_id');

            foreach ($groupedData as $stationId => $records) {
                $station = $records->first()->station;
                $stationName = $station->station_name;
                $lat = $station->lat ?? 23.8; // Default Latitude for Bangladesh

                $highTemps = $records->pluck('max_temp')
                    ->filter(fn($v) => !empty($v) && (float)$v != 0)
                    ->map(fn($v) => (float)$v)
                    ->sortDesc()
                    ->take($request->max_avg_value)
                    ->values()
                    ->toArray();

                $lowTemps = $records->pluck('mini_temp')
                    ->filter(fn($v) => !empty($v) && (float)$v != 0)
                    ->map(fn($v) => (float)$v)
                    ->sort()
                    ->take($request->min_avg_value)
                    ->values()
                    ->toArray();


                $highestMaxTemp = count($highTemps) ? max($highTemps) : 0;
                $lowestMiniTemp = count($lowTemps) ? min($lowTemps) : 0;

                $isPopulation = ($request->sd_type == '1');


                $highAvg = count($highTemps) ? array_sum($highTemps)/count($highTemps) : 0;
                $highStd = $this->calculateStandardDeviation($highTemps, $isPopulation);

                $lowAvg = count($lowTemps) ? array_sum($lowTemps)/count($lowTemps) : 0;
                $lowStd = $this->calculateStandardDeviation($lowTemps, $isPopulation);


                $maxAir98 = $highestMaxTemp + (2 * $highStd);
                $minAir98 = $lowestMiniTemp - (2 * $lowStd);

                $maxAir50 = $highestMaxTemp;
                $minAir50 = $lowestMiniTemp;

                $maxPVT_98 = $this->calculatePVT($maxAir98, $lat);
                $minPVT_98 = $this->calculatePVT($minAir98, $lat);

                $maxPVT_50 = $this->calculatePVT($maxAir50, $lat);
                $minPVT_50 = $this->calculatePVT($minAir50, $lat);


                $reportData[] = [
                    'station'  => $stationName,
                    'high_avg' => round($highAvg, 2),
                    'high_std' => round($highStd, 3),
                    'low_avg'  => round($lowAvg, 2),
                    'low_std'  => round($lowStd, 3),
                    'rel98'    => [
                        'max_air' => round($maxAir98, 2),
                        'max_pvt' => round($maxPVT_98, 2),
                        'min_air' => round($minAir98, 2),
                        'min_pvt' => round($minPVT_98, 2),
                    ],
                    'rel50'    => [
                        'max_air' => round($maxAir50, 2),
                        'max_pvt' => round($maxPVT_50, 2),
                        'min_air' => round($minAir50, 2),
                        'min_pvt' => round($minPVT_50, 2),
                    ]
                ];

                $stdChartData['labels'][] = $stationName;
                $stdChartData['high_std'][] = round($highStd, 3);
                $stdChartData['low_std'][] = round($lowStd, 3);

                $trendData[$stationName] = $records->map(function($record) {
                    return [
                        't' => $record->record_date,
                        'y' => round($record->max_temp, 2)
                    ];
                });
            }
        }

        return view('admin.weatherReports.index', compact('stations', 'reportData', 'trendData', 'stdChartData'));
    }



    private function calculatePVT($Ta, $lat) {
        // সূত্র: (Ta - 0.00618 * Lat^2 + 0.2289 * Lat + 42.2) * 0.9545 - 17.78
        $latSq = pow($lat, 2);
        $pvt = (($Ta - (0.00618 * $latSq) + (0.2289 * $lat) + 42.2) * 0.9545) - 17.78;
        return $pvt;
    }

    private function calculateStandardDeviation($array, $isPopulation = true) {
        if (empty($array)) return 0;
        $fMean = array_sum($array) / count($array);
        $fVariance = 0;
        foreach ($array as $i) {
            $fVariance += pow($i - $fMean, 2);
        }
        $divisor = $isPopulation ? count($array) : count($array) - 1;
        return sqrt($fVariance / ($divisor > 0 ? $divisor : 1));
    }


    private function getPercentile($data, $percentile) {
        if (empty($data)) return 0;
        sort($data);
        $count = count($data);
        $index = ($percentile / 100) * ($count - 1);
        $fraction = $index - floor($index);
        $lower = floor($index);
        $upper = ceil($index);

        return round($data[$lower] + ($fraction * ($data[$upper] - $data[$lower])), 2);
    }



    public function create()
    {
        abort_if(Gate::denies('weather_report_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.weatherReports.create');
    }

    public function store(StoreWeatherReportRequest $request)
    {
        $weatherReport = WeatherReport::create($request->all());

        return redirect()->route('admin.weather-reports.index');
    }

    public function edit(WeatherReport $weatherReport)
    {
        abort_if(Gate::denies('weather_report_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.weatherReports.edit', compact('weatherReport'));
    }

    public function update(UpdateWeatherReportRequest $request, WeatherReport $weatherReport)
    {
        $weatherReport->update($request->all());

        return redirect()->route('admin.weather-reports.index');
    }

    public function show(WeatherReport $weatherReport)
    {
        abort_if(Gate::denies('weather_report_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.weatherReports.show', compact('weatherReport'));
    }

    public function destroy(WeatherReport $weatherReport)
    {
        abort_if(Gate::denies('weather_report_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $weatherReport->delete();

        return back();
    }

    public function massDestroy(MassDestroyWeatherReportRequest $request)
    {
        $weatherReports = WeatherReport::find(request('ids'));

        foreach ($weatherReports as $weatherReport) {
            $weatherReport->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
