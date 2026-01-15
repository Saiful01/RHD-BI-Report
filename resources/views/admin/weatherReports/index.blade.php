@extends('layouts.admin')
@section('content')
    <div class="card">
        <div class="card-header">Weather Analysis & Trend Report</div>
        <div class="card-body">
            <form action="{{ route('admin.weather-reports.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <label>Stations (Multiple)</label>
                        <select name="station_ids[]" class="form-control select2" multiple required>
                            @foreach($stations as $id => $name)

                                @php
                                    $selectedStations = request('station_ids', []);
                                    if (empty(request()->all())) {

                                        $isDefault = ($loop->iteration <= 2);
                                    } else {
                                        $isDefault = in_array($id, $selectedStations);
                                    }
                                @endphp
                                <option value="{{ $id }}" {{ $isDefault ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>From Date</label>
                        <input type="date" name="from_date" class="form-control"
                               value="{{ request('from_date', '2020-01-01') }}" required>
                    </div>

                    <div class="col-md-2">
                        <label>To Date</label>
                        <input type="date" name="to_date" class="form-control"
                               value="{{ request('to_date', '2025-01-01') }}" required>
                    </div>

                    <div class="col-md-2">
                        <label>SD Type</label>
                        <select name="sd_type" class="form-control">
                            {{-- ১ পাঠালে Population, ০ পাঠালে Sample --}}
                            <option value="1" {{ request('sd_type', '1') == '1' ? 'selected' : '' }}>Population Standard Deviation</option>
                            <option value="0" {{ request('sd_type') == '0' ? 'selected' : '' }}>Sample Standard Deviation</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label>Max Count</label>
                        <input type="number" name="max_avg_value" class="form-control"
                               value="{{ request('max_avg_value', 7) }}">
                    </div>

                    <div class="col-md-1">
                        <label>Min Count</label>
                        <input type="number" name="min_avg_value" class="form-control"
                               value="{{ request('min_avg_value', 1) }}">
                    </div>

                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($reportData))
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center table-sm table-hover">
                                <thead class="bg-primary">
                                <tr>
                                    <th rowspan="3" class="align-middle">Location / Station</th>
                                    <th colspan="4" class="align-middle">Air Temperature Statistics (°C)</th>
                                    <th colspan="4" class="bg-warning">50% Reliability Temperature (°C)</th>
                                    <th colspan="4" class="bg-info text-white">98% Reliability Temperature (°C)</th>
                                </tr>
                                <tr>
                                    <th colspan="2">High Temp</th>
                                    <th colspan="2">Low Temp</th>
                                    <th colspan="2">Maximum</th>
                                    <th colspan="2">Minimum</th>
                                    <th colspan="2">Maximum</th>
                                    <th colspan="2">Minimum</th>
                                </tr>
                                <tr>
                                    <th>AVG</th>
                                    <th>STD</th>
                                    <th>AVG</th>
                                    <th>STD</th>
                                    <th>AIR</th>
                                    <th>PVT</th>
                                    <th>AIR</th>
                                    <th>PVT</th>
                                    <th>AIR</th>
                                    <th>PVT</th>
                                    <th>AIR</th>
                                    <th>PVT</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($reportData as $row)
                                    <tr>
                                        <td class="font-weight-bold text-left">{{ $row['station'] }}</td>
                                        {{-- Stats Section --}}
                                        <td>{{ $row['high_avg'] }}</td>
                                        <td>{{ $row['high_std'] }}</td>
                                        <td>{{ $row['low_avg'] }}</td>
                                        <td>{{ $row['low_std'] }}</td>

                                        {{-- 50% Reliability Section --}}
                                        <td class="table-warning">{{ $row['rel50']['max_air'] }}</td>
                                        <td class="table-warning">{{ $row['rel50']['max_pvt'] }}</td>
                                        <td class="table-warning">{{ $row['rel50']['min_air'] }}</td>
                                        <td class="table-warning">{{ $row['rel50']['min_pvt'] }}</td>

                                        {{-- 98% Reliability Section --}}
                                        <td class="table-info">{{ $row['rel98']['max_air'] }}</td>
                                        <td class="table-info">{{ $row['rel98']['max_pvt'] }}</td>
                                        <td class="table-info">{{ $row['rel98']['min_air'] }}</td>
                                        <td class="table-info">{{ $row['rel98']['min_pvt'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

           {{-- <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Temperature Variation Trend (Time Series)</div>
                    <div class="card-body">
                        <div style="height: 400px;">
                            <canvas id="weatherTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>--}}
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>



    <script>
        $(function () {
            const ctx = document.getElementById('weatherTrendChart');
            if(ctx) {
                const trendData = {!! json_encode($trendData) !!};
                const datasets = [];
                const colors = ['#FF6384', '#36A2EB', '#4BC0C0', '#FFCE56', '#9966FF', '#4CAF50', '#FF9800'];
                let colorIndex = 0;

                for (const [station, data] of Object.entries(trendData)) {
                    datasets.push({
                        label: station,
                        data: data.map(item => ({x: item.t, y: item.y})),
                        borderColor: colors[colorIndex % colors.length],
                        backgroundColor: colors[colorIndex % colors.length],
                        fill: false,
                        tension: 0.3, // লাইনকে স্মুথ করার জন্য
                        borderWidth: 2,
                        pointRadius: 0, // ডাটা বেশি হলে পয়েন্ট অফ রাখলে গ্রাফ ক্লিন দেখায়
                        pointHoverRadius: 5
                    });
                    colorIndex++;
                }

                new Chart(ctx, {
                    type: 'line',
                    data: { datasets: datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            x: {
                                type: 'category',
                                title: { display: true, text: 'Date' },
                                ticks: {
                                    maxRotation: 45,
                                    autoSkip: true,
                                    maxTicksLimit: 20 // ২ বছরের ডাটার ক্ষেত্রে এক্স-অক্ষ ক্লিন রাখবে
                                }
                            },
                            y: {
                                title: { display: true, text: 'Temperature (°C)' },
                                beginAtZero: false
                            }
                        },
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: { enabled: true }
                        }
                    }
                });
            }
        });
    </script>
@endsection
