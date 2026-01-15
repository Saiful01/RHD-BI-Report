@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.dailyWeather.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.daily-weathers.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.station') }}
                        </th>
                        <td>
                            {{ $dailyWeather->station->station_name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.max_temp') }}
                        </th>
                        <td>
                            {{ $dailyWeather->max_temp }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.mini_temp') }}
                        </th>
                        <td>
                            {{ $dailyWeather->mini_temp }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.avg_temp') }}
                        </th>
                        <td>
                            {{ $dailyWeather->avg_temp }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.humidity') }}
                        </th>
                        <td>
                            {{ $dailyWeather->humidity }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.dry_bulb') }}
                        </th>
                        <td>
                            {{ $dailyWeather->dry_bulb }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.dew_point') }}
                        </th>
                        <td>
                            {{ $dailyWeather->dew_point }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.total_rain_fall') }}
                        </th>
                        <td>
                            {{ $dailyWeather->total_rain_fall }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.total_sunshine_hour') }}
                        </th>
                        <td>
                            {{ $dailyWeather->total_sunshine_hour }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.record_date') }}
                        </th>
                        <td>
                            {{ $dailyWeather->record_date }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.daily-weathers.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection