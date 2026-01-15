@extends('layouts.admin')
@section('content')

    <div class="card">
        <div class="card-header">Search / Filter</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 form-group">
                    <label>Tender ID</label>
                    <select id="tenderid" class="form-control select2-ajax">
                        <option value=""></option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Ministry/Division</label>
                    <select id="ministry_division" class="form-control select2">
                        <option value="">All Ministry</option>
                        @foreach($ministries as $ministry)
                            <option value="{{ $ministry }}">{{ $ministry }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Supplier Name</label>
                    <select id="supplier_name" class="form-control select2">
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier }}">{{ $supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>District</label>
                    <select id="district" class="form-control select2">
                        <option value="">All District</option>
                        @foreach($districts as $district)
                            <option value="{{ $district }}">{{ $district }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>Method</label>
                    <select id="procurement_method" class="form-control select2">
                        <option value="">All Method</option>
                        @foreach($methods as $method)
                            <option value="{{ $method }}">{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>From Date</label>
                    <input type="date" id="from_date" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>To Date</label>
                    <input type="date" id="to_date" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>Package No</label>
                    <input type="text" id="package_no" class="form-control" placeholder="Enter Package No">
                </div>
                <div class="col-md-3 form-group">
                    <label>&nbsp;</label>
                    <div class="btn-group w-100">
                        <button type="button" id="filter_button" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button type="button" id="reset_button" class="btn btn-warning">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Tender List</div>
        <div class="card-body">
            <table class="table table-bordered table-striped table-hover ajaxTable datatable datatable-Tender">
                <thead>
                <tr>
                    <th width="10"></th>
                    <th>Tender ID</th>
                    <th>Package No</th>
                    <th>Ministry Division</th>
                    <th>Procuring Entity & Method</th>
                    <th>Notification Date</th>
                    <th>Contract Award to</th>
                    <th>Value (Cr. BDT)</th>
                    <th>Actions</th>
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

            $('.select2-ajax').select2({
                placeholder: "Type Tender ID...",
                minimumInputLength: 1,
                allowClear: true,
                ajax: {
                    url: "/admin/tender-select-search",
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {results: data};
                    },
                    cache: true
                }
            });

            let dtOverrideGlobals = {
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.tender.index') }}",
                    data: function (d) {
                        d.tenderid = $('#tenderid').val();
                        d.ministry_division = $('#ministry_division').val();
                        d.supplier_name = $('#supplier_name').val();
                        d.district = $('#district').val();
                        d.procurement_method = $('#procurement_method').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.package_no = $('#package_no').val();
                    }
                },
                columns: [
                    {data: 'placeholder', name: 'placeholder'},
                    {data: 'tenderid', name: 'tenderid'},
                    {data: 'tender_package_no', name: 'tender_package_no'},
                    {data: 'ministry_division', name: 'ministry_division'},
                    {data: 'entity_info', name: 'procuring_entity_code'},
                    {data: 'date_notification_award', name: 'date_notification_award'},
                    {data: 'supplier_name', name: 'supplier_name'},
                    {data: 'value_cr', name: 'contract_value'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ],
                pageLength: 25,
            };

            let table = $('.datatable-Tender').DataTable(dtOverrideGlobals);

            $('#filter_button').click(function () {
                table.ajax.reload();
            });


            $('#reset_button').click(function() {

                $('input').val('');
                $('select').val('').trigger('change');

                table.ajax.reload();
            });
        });
    </script>
@endsection
