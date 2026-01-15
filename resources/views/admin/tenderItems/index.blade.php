@extends('layouts.admin')
@section('content')

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Tender ID (Type to Search)</label>
                    <select id="tender_id_filter" class="form-control select2-ajax">
                        <option value="">Select Tender ID</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="button" id="filter_button" class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Tender Items List</div>
        <div class="card-body">
            <table class="table table-bordered table-striped ajaxTable datatable datatable-TenderItem">
                <thead>
                <tr>
                    <th width="10"></th> <th>Tender ID</th>
                    <th>Division</th>
                    <th>Supplier Name</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Unit</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Total Amount</th>
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
                ajax: {
                    url: "/admin/tender-select-search",
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });

            let dtOverrideGlobals = {
                processing: true,
                serverSide: true,
                retrieve: true,
                aaSorting: [],
                ajax: {
                    url: "{{ route('admin.tender-item.index') }}",
                    data: function (d) {

                        let urlParams = new URLSearchParams(window.location.search);
                        d.tender_id = urlParams.get('tender_id');
                        d.tender_id_filter = $('#tender_id_filter').val();
                    }
                },
                columns: [
                    { data: 'placeholder', name: 'placeholder', orderable: false, searchable: false },
                    { data: 'tender_id_display', name: 'tender.tenderid' },
                    { data: 'division_name', name: 'division.division' },
                    { data: 'supplier', name: 'tender.supplier_name' },
                    { data: 'item_code', name: 'item_code' },
                    { data: 'item_name', name: 'item_name' },
                    { data: 'item_unit', name: 'item_unit' },
                    { data: 'item_quantity', name: 'item_quantity' },
                    { data: 'item_rate', name: 'item_rate' },
                    { data: 'total_amount', name: 'total_amount', searchable: false }
                ],
                order: [[1, 'desc']],
                pageLength: 25,
            };

            let table = $('.datatable-TenderItem').DataTable(dtOverrideGlobals);

            $('#filter_button').click(function() {
                table.ajax.reload();
            });
        });
    </script>
@endsection
