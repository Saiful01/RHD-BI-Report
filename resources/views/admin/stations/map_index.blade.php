@extends('layouts.admin')

@section('styles')
    @parent
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />

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
        .table-hover tbody tr:hover { background-color: rgba(0, 123, 255, 0.05); }

        /* Map Container */
        #bangladesh-map {
            height: 600px;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            background: #f8f9fa; /* লোড হওয়ার আগে হালকা কালার */
            z-index: 1; /* অন্যান্য এলিমেন্টের নিচে রাখার জন্য */
        }

        .map-popup-card h6 { margin: 0 0 5px 0; color: #007bff; font-weight: bold; }
        .map-popup-card p { margin: 0 0 10px 0; font-size: 12px; color: #666; }

        .marker-cluster-small div, .marker-cluster-medium div, .marker-cluster-large div {
            background-color: #007bff !important;
            color: white !important;
        }
        .marker-cluster-small, .marker-cluster-medium, .marker-cluster-large {
            background-color: rgba(0, 123, 255, 0.2) !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list mr-1"></i> {{ trans('cruds.station.title_singular') }} {{ trans('global.list') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover datatable datatable-Station w-100">
                        <thead>
                        <tr>
                            <th width="10"></th>
                            <th>{{ trans('cruds.station.fields.station_name') }}</th>
                            <th>{{ trans('cruds.station.fields.lat') }}</th>
                            <th>{{ trans('cruds.station.fields.lon') }}</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($stations as $station)
                            <tr data-entry-id="{{ $station->id }}">
                                <td></td>
                                <td class="font-weight-bold">{{ $station->station_name ?? '' }}</td>
                                <td><span class="badge badge-light p-2">{{ $station->lat ?? 'N/A' }}</span></td>
                                <td><span class="badge badge-light p-2">{{ $station->lon ?? 'N/A' }}</span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a class="btn btn-primary" href="{{ route('admin.stations.show', $station->id) }}"><i class="fas fa-eye"></i></a>
                                        <a class="btn btn-info text-white" href="{{ route('admin.stations.edit', $station->id) }}"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.stations.destroy', $station->id) }}" method="POST" style="display: inline-block;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Sure?')"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
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
    </div>
@endsection

@section('scripts')
    @parent
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

    <script>
        $(document).ready(function () {
            // ম্যাপের জন্য কন্টেইনার চেক
            if ($('#bangladesh-map').length > 0) {

                const southWest = L.latLng(20.344, 88.010);
                const northEast = L.latLng(26.634, 92.673);
                const bounds = L.latLngBounds(southWest, northEast);

                const map = L.map('bangladesh-map', {
                    maxBounds: bounds,
                    maxBoundsViscosity: 1.0,
                    minZoom: 7,
                    scrollWheelZoom: false
                }).setView([23.6850, 90.3563], 7);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                const clusters = L.markerClusterGroup();

                const stationData = [
                        @foreach($stations as $station)
                        @if($station->lat && $station->lon)
                    {
                        lat: {{ $station->lat }},
                        lng: {{ $station->lon }},
                        name: "{{ $station->station_name }}",
                        url: "{{ route('admin.stations.show', $station->id) }}"
                    },
                    @endif
                    @endforeach
                ];

                stationData.forEach(function(s) {
                    const marker = L.marker([s.lat, s.lng]);
                    marker.bindPopup(`
                    <div class="map-popup-card">
                        <h6>${s.name}</h6>
                        <p>LAT: ${s.lat}<br>LON: ${s.lng}</p>
                        <a href="${s.url}" class="btn btn-sm btn-primary btn-block text-white">View Details</a>
                    </div>
                `);
                    clusters.addLayer(marker);
                });

                map.addLayer(clusters);

                // বাংলাদেশ বর্ডার ফিক্সড করার জন্য GeoJSON
                fetch('https://raw.githubusercontent.com/mahemoff/geodata/master/countries/bangladesh.geo.json')
                    .then(res => res.json())
                    .then(data => {
                        L.geoJSON(data, {
                            style: { color: "#007bff", weight: 1, fillOpacity: 0.01 }
                        }).addTo(map);
                    });

                // রেন্ডারিং ইস্যু ফিক্স (টাইমআউট দিয়ে ম্যাপ রিফ্রেশ)
                setTimeout(function() {
                    map.invalidateSize();
                }, 500);
            }
        });
    </script>
@endsection
