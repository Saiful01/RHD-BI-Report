@extends('layouts.admin')
@section('content')
{{--@can('daily_weather_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.daily-weathers.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.dailyWeather.title_singular') }}
            </a>
        </div>
    </div>
@endcan--}}

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label>Station</label>
                <select id="station_id" class="form-control select2">
                    <option value="">All Stations</option>
                    @foreach($stations as $id => $entry)
                        <option value="{{ $id }}">{{ $entry }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>From Date</label>
                <input type="date" id="from_date" class="form-control">
            </div>
            <div class="col-md-3">
                <label>To Date</label>
                <input type="date" id="to_date" class="form-control">
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <button type="button" id="filter_button" class="btn btn-primary btn-block">Filter</button>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        {{ trans('cruds.dailyWeather.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped table-hover ajaxTable datatable datatable-DailyWeather">
            <thead>
            <tr>
                <th width="10"></th>
                <th>{{ trans('cruds.dailyWeather.fields.station') }}</th>
                <th>{{ trans('cruds.dailyWeather.fields.max_temp') }}</th>
                <th>{{ trans('cruds.dailyWeather.fields.mini_temp') }}</th>
                <th>{{ trans('cruds.dailyWeather.fields.record_date') }}</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
        </table>
    </div>
</div>



@endsection
@section('scripts')
@parent
<script>
    $(function () {
        let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)

        let dtOverrideGlobals = {
            buttons: dtButtons,
            processing: true,
            serverSide: true,
            retrieve: true,
            aaSorting: [],
            ajax: {
                url: "{{ route('admin.daily-weathers.index') }}",
                data: function (d) {
                    d.station_id = $('#station_id').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                }
            },
            columns: [
                { data: 'placeholder', name: 'placeholder', orderable: false, searchable: false },
                { data: 'station_name', name: 'station.station_name' },
                { data: 'max_temp', name: 'max_temp' },
                { data: 'mini_temp', name: 'mini_temp' },
                { data: 'record_date', name: 'record_date' },
                { data: 'actions', name: '{{ trans('global.actions') }}', orderable: false, searchable: false }
            ],
            // ... বাকি কনফিগারেশন
        };

        let table = $('.datatable-DailyWeather').DataTable(dtOverrideGlobals);

// ফিল্টার বাটনে ক্লিক করলে টেবিল রিলোড হবে
        $('#filter_button').click(function() {
            table.ajax.reload();
        });


    });

</script>
@endsection
