@extends('layouts.admin')
@section('content')


    <div class="card">
        <div class="card-header">Search / Filter</div>

        <div class="card-body">
            <div class="row">

                <div class="col-md-3 mb-2">
                    <label>Tender ID</label>
                    <select id="tender_id_filter" class="form-control"></select>
                </div>

                <div class="col-md-3 mb-2">
                    <label>Division</label>
                    <select id="division_id" class="form-control">
                        <option value=""></option>
                        @foreach($divisions as $id => $entry)
                            <option value="{{ $id }}">{{ $entry }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label>Supplier Name</label>
                    <select id="supplier_name" class="form-control"></select>
                </div>

                <div class="col-md-3 mb-2">
                    <label>Item Code</label>
                    <select id="item_code" class="form-control"></select>
                </div>

                <div class="col-md-3 mb-2">
                    <label>Item Name</label>
                    <select id="item_name" class="form-control"></select>
                </div>

                <div class="col-md-3 mb-2">
                    <label>&nbsp;</label>
                    <div class="btn-group w-100">
                       {{-- <button id="filter_button" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>--}}
                        <button id="reset_button" class="btn btn-warning">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-header">Tender Item List</div>

        <div class="card-body">
            <table class="table table-bordered table-striped datatable datatable-TenderItem">
                <thead>
                <tr>
                    <th width="10"></th>
                    <th>Tender ID</th>
                    <th>Division</th>
                    <th>Supplier</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
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

            function select2Ajax(selector, url, placeholder) {
                $(selector).select2({
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true,
                    minimumInputLength: 1,
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term }),
                        processResults: function (data) {
                            // যদি backend numeric index পাঠায়, তাহলে text কে id হিসেবে use করো
                            const results = data.map(item => ({
                                id: item.text ?? item.id,  // id হিসেবে label পাঠাও
                                text: item.text ?? item.id
                            }));
                            return { results: results };
                        }
                    }
                });
            }




            select2Ajax('#tender_id_filter', '/admin/tender-select-search', 'Type Tender ID...');
            select2Ajax('#supplier_name', '/admin/supplier-search', 'Type Supplier Name...');
            select2Ajax('#item_code', '/admin/item-code-search', 'Type Item Code...');
            select2Ajax('#item_name', '/admin/item-name-search', 'Type Item Name...');

            /* Debugging Selected Value */
            $('#supplier_name, #item_code, #item_name').on('select2:select', function () {
                console.log('Selected on', this.id, ':', $(this).val());
            });


            $('#supplier_name, #item_code, #item_name').on('select2:unselect', function (e) {
                console.log('Unselected on', this.id, ':', $(this).val());
            });


            $('#division_id').select2({
                width: '100%',
                placeholder: 'Select Division',
                allowClear: true
            });

            let table = $('.datatable-TenderItem').DataTable({
                processing: true,
                serverSide: true,



                ajax: {
                    url: "{{ route('admin.tender-item.index') }}",
                    data: function (d) {
                        d.tender_id_filter = $('#tender_id_filter').val();
                        d.division_id      = $('#division_id').val();
                        d.supplier_name    = $('#supplier_name').val();
                        d.item_code        = $('#item_code').val();
                        d.item_name        = $('#item_name').val();
                    }
                },

                columns: [
                    { data: 'placeholder', name: 'placeholder' },
                    { data: 'tender_id_display', name: 'tender.tenderid' },
                    { data: 'division_name', name: 'division.division' },
                    { data: 'supplier', name: 'tender.supplier_name' },
                    { data: 'item_code', name: 'item_code' },
                    { data: 'item_name', name: 'item_name' },
                    { data: 'item_quantity', name: 'item_quantity' },
                    { data: 'item_rate', name: 'item_rate' },
                    { data: 'total_amount', name: 'total_amount', orderable: false, searchable: false },
                ]
            });

            /* Search */
            $('#filter_button').on('click', function () {
                table.ajax.reload();
            });

            /* Auto reload on change */
            $('#tender_id_filter, #division_id, #supplier_name, #item_code, #item_name')
                .on('change', function () {
                    table.ajax.reload();
                });

            /* Reset */
            $('#reset_button').on('click', function () {
                $('#tender_id_filter, #division_id, #supplier_name, #item_code, #item_name')
                    .val(null).trigger('change');

                table.ajax.reload();
            });

        });
    </script>
@endsection


