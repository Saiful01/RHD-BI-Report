@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.station.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.stations.update", [$station->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="station_name">{{ trans('cruds.station.fields.station_name') }}</label>
                <input class="form-control {{ $errors->has('station_name') ? 'is-invalid' : '' }}" type="text" name="station_name" id="station_name" value="{{ old('station_name', $station->station_name) }}" required>
                @if($errors->has('station_name'))
                    <div class="invalid-feedback">
                        {{ $errors->first('station_name') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.station.fields.station_name_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="lat">{{ trans('cruds.station.fields.lat') }}</label>
                <input class="form-control {{ $errors->has('lat') ? 'is-invalid' : '' }}" type="number" name="lat" id="lat" value="{{ old('lat', $station->lat) }}" step="0.00001">
                @if($errors->has('lat'))
                    <div class="invalid-feedback">
                        {{ $errors->first('lat') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.station.fields.lat_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="lon">{{ trans('cruds.station.fields.lon') }}</label>
                <input class="form-control {{ $errors->has('lon') ? 'is-invalid' : '' }}" type="number" name="lon" id="lon" value="{{ old('lon', $station->lon) }}" step="0.00001">
                @if($errors->has('lon'))
                    <div class="invalid-feedback">
                        {{ $errors->first('lon') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.station.fields.lon_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="elevation">{{ trans('cruds.station.fields.elevation') }}</label>
                <input class="form-control {{ $errors->has('elevation') ? 'is-invalid' : '' }}" type="number" name="elevation" id="elevation" value="{{ old('elevation', $station->elevation) }}" step="0.00001">
                @if($errors->has('elevation'))
                    <div class="invalid-feedback">
                        {{ $errors->first('elevation') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.station.fields.elevation_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.station.fields.status') }}</label>
                @foreach(App\Models\Station::STATUS_RADIO as $key => $label)
                    <div class="form-check {{ $errors->has('status') ? 'is-invalid' : '' }}">
                        <input class="form-check-input" type="radio" id="status_{{ $key }}" name="status" value="{{ $key }}" {{ old('status', $station->status) === (string) $key ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_{{ $key }}">{{ $label }}</label>
                    </div>
                @endforeach
                @if($errors->has('status'))
                    <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.station.fields.status_helper') }}</span>
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