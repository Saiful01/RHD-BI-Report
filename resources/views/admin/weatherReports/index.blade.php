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
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center table-sm table-hover mb-0" id="weatherTable">
                                <thead class="bg-primary text-white">
                                <tr>
                                    <th rowspan="3" class="align-middle" style="min-width: 180px;">Location / Station</th>
                                    <th colspan="4" class="align-middle">Air Temperature Statistics (°C)</th>
                                    <th colspan="4" class="bg-warning text-dark">50% Reliability (°C)</th>
                                    <th colspan="4" class="bg-info text-white">98% Reliability (°C)</th>
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
                                    <th>STD <i class="fas fa-info-circle text-white-50" title="Click individual STD for detail"></i></th>
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
                                        <td class="font-weight-bold text-left align-middle px-3">
                                            {{ $row['station'] }}
                                            <button class="btn btn-xs btn-outline-secondary float-right"
                                                    onclick="openCombinedStdModal('{{ $row['station'] }}', {{ $row['high_avg'] }}, {{ $row['high_std'] }}, {{ $row['low_avg'] }}, {{ $row['low_std'] }})"
                                                    title="Compare Day/Night Distribution">
                                                <i class="fas fa-balance-scale"></i> Compare
                                            </button>
                                        </td>
                                        {{-- Stats Section --}}
                                        <td class="align-middle">{{ $row['high_avg'] }}</td>
                                        <td class="align-middle text-primary font-weight-bold" style="cursor: pointer; background: rgba(0,123,255,0.05)"
                                            onclick="openSingleStdModal('{{ $row['station'] }}', {{ $row['high_avg'] }}, {{ $row['high_std'] }}, 'High Temp')">
                                            {{ $row['high_std'] }} <i class="fas fa-chart-line fa-xs"></i>
                                        </td>
                                        <td class="align-middle">{{ $row['low_avg'] }}</td>
                                        <td class="align-middle text-info font-weight-bold" style="cursor: pointer; background: rgba(23,162,184,0.05)"
                                            onclick="openSingleStdModal('{{ $row['station'] }}', {{ $row['low_avg'] }}, {{ $row['low_std'] }}, 'Low Temp')">
                                            {{ $row['low_std'] }} <i class="fas fa-chart-line fa-xs"></i>
                                        </td>

                                        {{-- 50% Section --}}
                                        <td class="table-warning align-middle">{{ $row['rel50']['max_air'] }}</td>
                                        <td class="table-warning align-middle">{{ $row['rel50']['max_pvt'] }}</td>
                                        <td class="table-warning align-middle">{{ $row['rel50']['min_air'] }}</td>
                                        <td class="table-warning align-middle">{{ $row['rel50']['min_pvt'] }}</td>

                                        {{-- 98% Section --}}
                                        <td class="table-info align-middle">{{ $row['rel98']['max_air'] }}</td>
                                        <td class="table-info align-middle">{{ $row['rel98']['max_pvt'] }}</td>
                                        <td class="table-info align-middle">{{ $row['rel98']['min_air'] }}</td>
                                        <td class="table-info align-middle">{{ $row['rel98']['min_pvt'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">Overall Standard Deviation Comparison (Bar View)</div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="stdComparisonChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Reusable Modal --}}
    <div class="modal fade" id="stdDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Analysis</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div style="height: 400px;">
                        <canvas id="normalDistChart"></canvas>
                    </div>
                    <div id="statusAlert" class="alert mt-3 text-center" role="alert" style="display:none;">
                        <h6 id="statusTitle" class="font-weight-bold mb-1"></h6>
                        <p id="statusMessage" class="mb-0 small"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let normalChartInstance = null;

        // --- ফাংশন ১: সিঙ্গেল বেল কার্ভ (হাই অথবা লো এর জন্য) ---
        function openSingleStdModal(station, mean, std, type) {
            $('#modalTitle').text(`${type} Standard Deviation: ${station}`);
            setupChart(station, mean, std, type, false);
        }

        // --- ফাংশন ২: কম্বাইন্ড বেল কার্ভ (কম্পেয়ার বাটন) ---
        function openCombinedStdModal(station, hMean, hStd, lMean, lStd) {
            $('#modalTitle').text(`Day/Night Distribution Comparison: ${station}`);
            setupChart(station, hMean, hStd, 'High Temp', true, lMean, lStd);
        }

        // --- কোর চার্ট ইঞ্জিন ---
        function setupChart(station, mean, std, type, isCombined, lMean = 0, lStd = 0) {
            $('#stdDetailModal').modal('show');
            const ctx = document.getElementById('normalDistChart').getContext('2d');
            if (normalChartInstance) normalChartInstance.destroy();

            const labels = [];
            const datasets = [];

            // রেঞ্জ নির্ধারণ
            const start = isCombined ? (lMean - 4 * lStd) : (mean - 4 * std);
            const end = isCombined ? (mean + 4 * std) : (mean + 4 * std);
            const step = (end - start) / 100;

            const highData = [];
            const lowData = [];

            for (let i = start; i <= end; i += step) {
                labels.push(i.toFixed(2));

                // High Curve
                const hExp = -Math.pow(i - mean, 2) / (2 * Math.pow(std, 2));
                highData.push((1 / (std * Math.sqrt(2 * Math.PI))) * Math.exp(hExp));

                // Low Curve (If combined)
                if(isCombined) {
                    const lExp = -Math.pow(i - lMean, 2) / (2 * Math.pow(lStd, 2));
                    lowData.push((1 / (lStd * Math.sqrt(2 * Math.PI))) * Math.exp(lExp));
                }
            }

            datasets.push({
                label: isCombined ? `Day (Avg: ${mean})` : `${type} (Avg: ${mean})`,
                data: highData,
                borderColor: '#FF6384',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: true, tension: 0.4, pointRadius: 0
            });

            if(isCombined) {
                datasets.push({
                    label: `Night (Avg: ${lMean})`,
                    data: lowData,
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true, tension: 0.4, pointRadius: 0
                });
            }

            normalChartInstance = new Chart(ctx, {
                type: 'line',
                data: { labels: labels, datasets: datasets },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { display: false }, x: { title: { display: true, text: 'Temperature (°C)' } } }
                }
            });

            updateStatus(std, isCombined ? lStd : null);
        }

        function updateStatus(hStd, lStd) {
            const statusAlert = $('#statusAlert');
            statusAlert.show().removeClass('alert-success alert-warning alert-danger');

            const checkStd = lStd ? Math.max(hStd, lStd) : hStd;

            if (checkStd < 1.5) {
                statusAlert.addClass('alert-success');
                $('#statusTitle').text('Status: Highly Stable');
                $('#statusMessage').text('Minimal temperature variation. Standard binder is sufficient.');
            } else if (checkStd < 3) {
                statusAlert.addClass('alert-warning');
                $('#statusTitle').text('Status: Moderate Variation');
                $('#statusMessage').text('Some fluctuations observed. Standard binder should suffice but check local conditions.');
            } else {
                statusAlert.addClass('alert-danger');
                $('#statusTitle').text('Status: High Volatility');
                $('#statusMessage').text('High temperature variation detected! Consider modified binder or pavement reinforcement.');
            }
        }

        $(function () {
            const stdCtx = document.getElementById('stdComparisonChart');
            if(stdCtx) {
                const stdData = {!! json_encode($stdChartData) !!};
                new Chart(stdCtx, {
                    type: 'line', // বার চার্ট থেকে লাইন চার্টে পরিবর্তন
                    data: {
                        labels: stdData.labels,
                        datasets: [
                            {
                                label: 'High Temp STD',
                                data: stdData.high_std,
                                borderColor: '#FF6384', // লাইনের রঙ
                                backgroundColor: 'rgba(255, 99, 132, 0.1)', // লাইনের নিচের হালকা ছায়া
                                fill: true, // এরিয়া ফিল করার জন্য
                                tension: 0.4, // কার্ভ বা বাঁকানোর মাত্রা
                                borderWidth: 3,
                                pointRadius: 4,
                                pointBackgroundColor: '#FF6384'
                            },
                            {
                                label: 'Low Temp STD',
                                data: stdData.low_std,
                                borderColor: '#36A2EB', // লাইনের রঙ
                                backgroundColor: 'rgba(54, 162, 235, 0.1)', // লাইনের নিচের হালকা ছায়া
                                fill: true, // এরিয়া ফিল করার জন্য
                                tension: 0.4, // কার্ভ বা বাঁকানোর মাত্রা
                                borderWidth: 3,
                                pointRadius: 4,
                                pointBackgroundColor: '#36A2EB'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'STD Value'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Stations'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
