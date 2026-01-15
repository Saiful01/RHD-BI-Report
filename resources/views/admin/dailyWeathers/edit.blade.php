@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.dailyWeather.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.daily-weathers.update", [$dailyWeather->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="station_id">{{ trans('cruds.dailyWeather.fields.station') }}</label>
                <select class="form-control select2 {{ $errors->has('station') ? 'is-invalid' : '' }}" name="station_id" id="station_id" required>
                    @foreach($stations as $id => $entry)
                        <option value="{{ $id }}" {{ (old('station_id') ? old('station_id') : $dailyWeather->station->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('station'))
                    <div class="invalid-feedback">
                        {{ $errors->first('station') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dailyWeather.fields.station_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="max_temp">{{ trans('cruds.dailyWeather.fields.max_temp') }}</label>
                <input class="form-control {{ $errors->has('max_temp') ? 'is-invalid' : '' }}" type="number" name="max_temp" id="max_temp" value="{{ old('max_temp', $dailyWeather->max_temp) }}" step="0.00001">
                @if($errors->has('max_temp'))
                    <div class="invalid-feedback">
                        {{ $errors->first('max_temp') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dailyWeather.fields.max_temp_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="mini_temp">{{ trans('cruds.dailyWeather.fields.mini_temp') }}</label>
                <input class="form-control {{ $errors->has('mini_temp') ? 'is-invalid' : '' }}" type="number" name="mini_temp" id="mini_temp" value="{{ old('mini_temp', $dailyWeather->mini_temp) }}" step="0.00001">
                @if($errors->has('mini_temp'))
                    <div class="invalid-feedback">
                        {{ $errors->first('mini_temp') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dailyWeather.fields.mini_temp_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="avg_temp">{{ trans('cruds.dailyWeather.fields.avg_temp') }}</label>
                <input class="form-control {{ $errors->has('avg_temp') ? 'is-invalid' : '' }}" type="number" name="avg_temp" id="avg_temp" value="{{ old('avg_temp', $dailyWeather->avg_temp) }}" step="0.00001">
                @if($errors->has('avg_temp'))
                    <div class="invalid-feedback">
                        {{ $errors->first('avg_temp') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dailyWeather.fields.avg_temp_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="humidity">{{ trans('cruds.dailyWeather.fields.humidity') }}</label>
                <input class="form-control {{ $errors->has('humidity') ? 'is-invalid' : '' }}" type="number" name="humidity" id="humidity" value="{{ old('humidity', $dailyWeather->humidity) }}" step="0.00001">
                @if($errors->has('humidity'))
                    <div class="invalid-feedback">
                        {{ $errors->first('humidity') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dailyWeather.fields.humidity_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="dry_bulb">{{ trans('cruds.dailyWeather.fields.dry_bulb') }}</label>
                <input class="form-control {{ $errors->has('dry_bulb') ? 'is-invalid' : '' }}" type="number" name="dry_bulb" id="dry_bulb" value="{{ old('dry_bulb', $dailyWeather->dry_bulb) }}" step="0.00001">
                @if($errors->has('dry_bulb'))
                    <div class="invalid-feedback">
                        {{ $errors->first('dry_bulb') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dailyWeather.fields.dry_bulb_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="dew_point">{{ trans('cruds.dailyWeather.fields.dew_point') }}</label>
                <input class="form-control {{ $errors->has('dew_point') ? 'is-invalid' : '' }}" type="number" name="dew_point" id="dew_point" value="{{ old('dew_point', $dailyWeather->dew_point) }}" step="0.00001">
                @if($errors->has('dew_point'))
                    <div class="invalid-feedback">
                        {{ $errors->first('dew_point') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dailyWeather.fields.dew_point_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="total_rain_fall">{{ trans('cruds.dailyWeather.fields.total_rain_fall') }}</label>
                <input class="form-control {{ $errors->has('total_rain_fall') ? 'is-invalid' : '' }}" type="number" name="total_rain_fall" id="total_rain_fall" value="{{ old('total_rain_fall', $dailyWeather->total_rain_fall) }}" step="0.01">
                @if($errors->has('total_rain_fall'))
                    <div class="invalid-feedback">
                        {{ $errors->first('total_rain_fall') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dailyWeather.fields.total_rain_fall_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="total_sunshine_hour">{{ trans('cruds.dailyWeather.fields.total_sunshine_hour') }}</label>
                <input class="form-control {{ $errors->has('total_sunshine_hour') ? 'is-invalid' : '' }}" type="number" name="total_sunshine_hour" id="total_sunshine_hour" value="{{ old('total_sunshine_hour', $dailyWeather->total_sunshine_hour) }}" step="0.01">
                @if($errors->has('total_sunshine_hour'))
                    <div class="invalid-feedback">
                        {{ $errors->first('total_sunshine_hour') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dailyWeather.fields.total_sunshine_hour_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="record_date">{{ trans('cruds.dailyWeather.fields.record_date') }}</label>
                <input class="form-control date {{ $errors->has('record_date') ? 'is-invalid' : '' }}" type="text" name="record_date" id="record_date" value="{{ old('record_date', $dailyWeather->record_date) }}" required>
                @if($errors->has('record_date'))
                    <div class="invalid-feedback">
                        {{ $errors->first('record_date') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dailyWeather.fields.record_date_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



@endsection