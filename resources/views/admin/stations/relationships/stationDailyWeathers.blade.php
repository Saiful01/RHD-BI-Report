@can('daily_weather_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.daily-weathers.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.dailyWeather.title_singular') }}
            </a>
        </div>
    </div>
@endcan

<div class="card">
    <div class="card-header">
        {{ trans('cruds.dailyWeather.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-stationDailyWeathers">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.station') }}
                        </th>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.max_temp') }}
                        </th>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.mini_temp') }}
                        </th>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.avg_temp') }}
                        </th>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.humidity') }}
                        </th>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.dry_bulb') }}
                        </th>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.dew_point') }}
                        </th>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.total_rain_fall') }}
                        </th>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.total_sunshine_hour') }}
                        </th>
                        <th>
                            {{ trans('cruds.dailyWeather.fields.record_date') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyWeathers as $key => $dailyWeather)
                        <tr data-entry-id="{{ $dailyWeather->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $dailyWeather->station->station_name ?? '' }}
                            </td>
                            <td>
                                {{ $dailyWeather->max_temp ?? '' }}
                            </td>
                            <td>
                                {{ $dailyWeather->mini_temp ?? '' }}
                            </td>
                            <td>
                                {{ $dailyWeather->avg_temp ?? '' }}
                            </td>
                            <td>
                                {{ $dailyWeather->humidity ?? '' }}
                            </td>
                            <td>
                                {{ $dailyWeather->dry_bulb ?? '' }}
                            </td>
                            <td>
                                {{ $dailyWeather->dew_point ?? '' }}
                            </td>
                            <td>
                                {{ $dailyWeather->total_rain_fall ?? '' }}
                            </td>
                            <td>
                                {{ $dailyWeather->total_sunshine_hour ?? '' }}
                            </td>
                            <td>
                                {{ $dailyWeather->record_date ?? '' }}
                            </td>
                            <td>
                                @can('daily_weather_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.daily-weathers.show', $dailyWeather->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('daily_weather_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.daily-weathers.edit', $dailyWeather->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('daily_weather_delete')
                                    <form action="{{ route('admin.daily-weathers.destroy', $dailyWeather->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('daily_weather_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.daily-weathers.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    orderCellsTop: true,
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  let table = $('.datatable-stationDailyWeathers:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection