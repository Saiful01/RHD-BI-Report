@extends('layouts.admin')
@section('content')

    <div class="card">
        <div class="card-header">Summary Report Filter</div>
        <div class="card-body">
            <div class="row">

                {{-- Tender ID --}}
                <div class="col-md-3 mb-2">
                    <label>Tender ID</label>
                    <select id="tender_id_filter" class="form-control"></select>
                </div>

                {{-- Division (multiple) --}}
                <div class="col-md-3 mb-2">
                    <label>Division</label>
                    <select id="division_id" class="form-control" multiple>
                        @foreach($divisions as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Ministry --}}
                <div class="col-md-3 mb-2">
                    <label>Ministry</label>
                    <select id="ministry" class="form-control" multiple>
                        @foreach($ministries as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- District --}}
                <div class="col-md-3 mb-2">
                    <label>District</label>
                    <select id="district" class="form-control" multiple>
                        @foreach($districts as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Supplier --}}
                <div class="col-md-3 mb-2">
                    <label>Supplier</label>
                    <select id="supplier_name" class="form-control" multiple></select>
                </div>

                {{-- Item Code --}}
                <div class="col-md-3 mb-2">
                    <label>Item Code</label>
                    <select id="item_code" class="form-control" multiple></select>
                </div>

                {{-- Item Name --}}
                <div class="col-md-3 mb-2">
                    <label>Item Name</label>
                    <select id="item_name" class="form-control" multiple></select>
                </div>

                {{-- Buttons --}}
                <div class="col-md-3 mb-2">
                    <label>&nbsp;</label>
                    <div class="btn-group w-100">
                        <button id="filter_button" class="btn btn-primary">Search</button>
                        <button id="reset_button" class="btn btn-warning">Reset</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Report Area --}}
    <div class="card mt-3">
        <div class="card-header">Report</div>
        <div id="report_area"></div>

        <div class="card-body" id="report_area">
            <p>Select filters and click "Search" to see report.</p>
        </div>
    </div>

@endsection

@section('scripts')
    @parent
    <script>
        $(function(){

            function select2AjaxMultiple(selector, url, placeholder) {
                $(selector).select2({
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true,
                    multiple: true,
                    minimumInputLength: 1,
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term }),
                        processResults: function (data) {
                            return {
                                results: data.map(item => ({
                                    id: item.text,
                                    text: item.text
                                }))
                            };
                        }
                    }
                });
            }



            // AJAX Select2
            select2AjaxMultiple('#tender_id_filter', '/admin/tender-select-search', 'Type Tender ID...');
            select2AjaxMultiple('#supplier_name', '/admin/supplier-search', 'Select Supplier...');
            select2AjaxMultiple('#item_code', '/admin/item-code-search', 'Select Item Code...');
            select2AjaxMultiple('#item_name', '/admin/item-name-search', 'Select Item Name...');

            $('#division_id, #ministry, #district').select2({
                width: '100%',
                placeholder: 'Select...',
                allowClear: true,
                multiple: true
            });

            // Fetch Report
            function fetchReport() {
                let data = {
                    tender_id_filter: $('#tender_id_filter').val(),
                    division_id: $('#division_id').val(),
                    ministry: $('#ministry').val(),
                    district: $('#district').val(),
                    supplier_name: $('#supplier_name').val(),
                    item_code: $('#item_code').val(),
                    item_name: $('#item_name').val()
                };

                $('#report_area').html('<p>Loading...</p>');

                $.ajax({
                    url: "{{ route('admin.tender-item.summeryReport') }}",
                    data: data,
                    success: function(response){

                        let divisions = Object.keys(response.summary)
                            .filter(d => d)
                            .sort();

                        let summaryHtml = '<div class="row mb-4 p-2">';

                        divisions.forEach(function(division){
                            summaryHtml += `
                    <div class="col-md-2 mb-2">
                        <div class="card text-center shadow-sm">
                            <div class="card-body p-2">
                                <h6 class="card-title mb-2">${division}</h6>
                                <span class="badge bg-primary text-white fs-6">
                                    ${response.summary[division]} item(s)
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                        });

                        summaryHtml += '</div>';


                        let reportHtml = '';
                        let grouped = response.grouped;

                        divisions.forEach(function(division){
                            reportHtml += `<h5 class="mt-4 mb-2 p-2">${division}</h5>`;
                            reportHtml += `<div class="table-responsive p-2">`; //
                            reportHtml += `<table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Tender ID</th>
                                <th>Supplier</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Rate</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>`;

                            grouped[division].forEach(row => {
                                reportHtml += `<tr>
                            <td>${row.tender?.tenderid ?? 'N/A'}</td>
                            <td>${row.tender?.supplier_name ?? 'N/A'}</td>
                            <td>${row.item_code}</td>
                            <td>${row.item_name}</td>
                            <td>${parseFloat(row.item_quantity).toFixed(2)}</td>
                            <td>${parseFloat(row.item_rate).toFixed(2)}</td>
                            <td>${(row.item_quantity * row.item_rate).toFixed(2)}</td>
                       </tr>`;
                            });

                            reportHtml += `</tbody></table></div>`;
                        });


                        if(divisions.length === 0) {
                            reportHtml = '<p>No data found.</p>';
                        }

                        $('#report_area').html(summaryHtml + reportHtml);
                    },
                    error: function() {
                        $('#report_area').html('<p class="text-danger">Error loading data.</p>');
                    }
                });
            }




            $('#filter_button').on('click', fetchReport);

            $('#reset_button').on('click', function(){
                $('#tender_id_filter, #division_id, #ministry, #district, #supplier_name, #item_code, #item_name')
                    .val(null).trigger('change');
                $('#report_area').html('<p></p>');
            });

        });
    </script>
@endsection
