@extends('layouts.admin')

@section('styles')
    @parent
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* Table Styling */
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        /* Map Container */
        #bangladesh-map {
            height: 600px;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            background: #ffffff;
            z-index: 1;
        }

        .map-popup-card h6 { margin: 0 0 5px 0; color: #007bff; font-weight: bold; }
        .map-popup-card p { margin: 0 0 10px 0; font-size: 12px; color: #666; }
    </style>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            {{ trans('cruds.station.title_singular') }} {{ trans('global.list') }}
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover datatable datatable-Station w-100">
                    <thead>
                    <tr>
                        <th width="10"></th>
                        <th>{{ trans('cruds.station.fields.station_name') }}</th>
                        <th>{{ trans('cruds.station.fields.lat') }}</th>
                        <th>{{ trans('cruds.station.fields.lon') }}</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($stations as $key => $station)
                        <tr data-entry-id="{{ $station->id }}">
                            <td></td>
                            <td>{{ $station->station_name ?? '' }}</td>
                            <td>{{ $station->lat ?? '' }}</td>
                            <td>{{ $station->lon ?? '' }}</td>
                            <td>
                                @can('station_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.stations.show', $station->id) }}">{{ trans('global.view') }}</a>
                                @endcan
                                @can('station_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.stations.edit', $station->id) }}">{{ trans('global.edit') }}</a>
                                @endcan
                                @can('station_delete')
                                    <form action="{{ route('admin.stations.destroy', $station->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-map-marked-alt mr-1"></i> Station Geographic View
            </h5>
        </div>
        <div class="card-body p-2">
            <div id="bangladesh-map"></div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        $(document).ready(function () {

            if (!$('#bangladesh-map').length) return;

            // Bangladesh bounding box
            const bdBounds = L.latLngBounds(
                [20.6708, 88.0100],
                [26.6345, 92.6736]
            );

            // Map init
            const map = L.map('bangladesh-map', {
                center: [23.6850, 90.3563],
                zoom: 7,
                minZoom: 7,
                maxZoom: 12,
                maxBounds: bdBounds,
                maxBoundsViscosity: 1.0,
                scrollWheelZoom: false
            });

            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; CARTO'
            }).addTo(map);

            const markers = [];

            // Add markers (ALL ARE BD – already filtered)
            @foreach($stations as $station)
                @if($station->lat && $station->lon)
            (function () {
                const lat = Number("{{ $station->lat }}");
                const lon = Number("{{ $station->lon }}");

                const marker = L.marker([lat, lon]).addTo(map);

                marker.bindPopup(`
                <strong>{{ $station->station_name }}</strong><br>
                Lat: ${lat}<br>
                Lon: ${lon}
            `);

                markers.push([lat, lon]);
            })();
            @endif
            @endforeach

            // Auto zoom
            if (markers.length > 0) {
                map.fitBounds(L.latLngBounds(markers).pad(0.1));
            }

            // Bangladesh border (visual only)
            fetch('https://raw.githubusercontent.com/mahemoff/geodata/master/countries/bangladesh.geo.json')
                .then(res => res.json())
                .then(data => {
                    L.geoJSON(data, {
                        style: { color: '#007bff', weight: 2, fillOpacity: 0.05 }
                    }).addTo(map);
                });

            setTimeout(() => map.invalidateSize(), 500);
        });
        /* ============================
           DATATABLE SCRIPT
        ============================ */

        $(function () {
            let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)

            @can('station_delete')
            let deleteButton = {
                text: '{{ trans('global.datatables.delete') }}',
                url: "{{ route('admin.stations.massDestroy') }}",
                className: 'btn-danger',
                action: function (e, dt) {
                    let ids = $.map(
                        dt.rows({ selected: true }).nodes(),
                        entry => $(entry).data('entry-id')
                    );

                    if (ids.length === 0) {
                        alert('{{ trans('global.datatables.zero_selected') }}');
                        return;
                    }

                    if (confirm('{{ trans('global.areYouSure') }}')) {
                        $.ajax({
                            headers: { 'x-csrf-token': _token },
                            method: 'POST',
                            url: this.url,
                            data: { ids: ids, _method: 'DELETE' }
                        }).done(() => location.reload());
                    }
                }
            }
            dtButtons.push(deleteButton)
            @endcan

            $.extend(true, $.fn.dataTable.defaults, {
                orderCellsTop: true,
                ordering: false,
                pageLength: 100
            });

            $('.datatable-Station:not(.ajaxTable)').DataTable({
                buttons: dtButtons
            });
        });
    </script>
@endsection

