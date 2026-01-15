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
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class DailyWeatherController extends Controller
{


    public function index(Request $request)
    {
        abort_if(Gate::denies('daily_weather_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = DailyWeather::with(['station'])->select('daily_weathers.*');

            // ফিল্টার লজিক
            if (!empty($request->station_id)) {
                $query->where('station_id', $request->station_id);
            }
            if (!empty($request->from_date) && !empty($request->to_date)) {
                $query->whereBetween('record_date', [$request->from_date, $request->to_date]);
            }

            return DataTables::of($query)
                // এই লাইনটি মাস্ট লাগবেই
                ->addColumn('placeholder', '&nbsp;')
                ->addColumn('station_name', function ($row) {
                    return $row->station ? $row->station->station_name : '';
                })
                ->addColumn('actions', '&nbsp;')
                ->editColumn('actions', function ($row) {
                    $viewGate      = 'daily_weather_show';
                    $editGate      = 'daily_weather_edit';
                    $deleteGate    = 'daily_weather_delete';
                    $crudRoutePart = 'daily-weathers';
                    return view('partials.datatablesActions', compact('viewGate', 'editGate', 'deleteGate', 'crudRoutePart', 'row'));
                })
                ->rawColumns(['actions', 'placeholder'])
                ->make(true);
        }

        $stations = Station::pluck('station_name', 'id');
        return view('admin.dailyWeathers.index', compact('stations'));
    }

    public function create()
    {
        abort_if(Gate::denies('daily_weather_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stations = Station::pluck('station_name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.dailyWeathers.create', compact('stations'));
    }

    public function store(StoreDailyWeatherRequest $request)
    {
        $dailyWeather = DailyWeather::create($request->all());

        return redirect()->route('admin.daily-weathers.index');
    }

    public function edit(DailyWeather $dailyWeather)
    {
        abort_if(Gate::denies('daily_weather_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stations = Station::pluck('station_name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $dailyWeather->load('station');

        return view('admin.dailyWeathers.edit', compact('dailyWeather', 'stations'));
    }

    public function update(UpdateDailyWeatherRequest $request, DailyWeather $dailyWeather)
    {
        $dailyWeather->update($request->all());

        return redirect()->route('admin.daily-weathers.index');
    }

    public function show(DailyWeather $dailyWeather)
    {
        abort_if(Gate::denies('daily_weather_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dailyWeather->load('station');

        return view('admin.dailyWeathers.show', compact('dailyWeather'));
    }

    public function destroy(DailyWeather $dailyWeather)
    {
        abort_if(Gate::denies('daily_weather_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dailyWeather->delete();

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
}
