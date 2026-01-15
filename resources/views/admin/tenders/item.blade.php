@extends('layouts.admin')
@section('content')

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <strong>Bill of Quantities (BOQ) - Tender ID: {{ $tender->tenderid }}</strong>
            <a href="{{ route('admin.tender.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
        </div>

        <div class="card-body">
            <h5 class="text-primary border-bottom pb-2 mb-3">
                <i class="fas fa-info-circle"></i> Tender  Basic Information
            </h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <tbody>
                    <tr><th class="" width="30%">Tender ID</th><td>{{ $tender->tenderid }}</td></tr>
                    <tr><th class="">Ministry / Division</th><td>{{ $tender->ministry_division }}</td></tr>
                    <tr><th class="">Agency</th><td>{{ $tender->agency }}</td></tr>
                    <tr><th class="" width="30%">Procuring Entity Name</th><td>{{ $tender->procuring_entity_name }} ({{ $tender->procuring_entity_code }})</td></tr>
                    <tr><th class="">Procuring Entity District</th><td>{{ $tender->procuring_entity_district }}</td></tr>
                    <tr><th class="">Procurement Method</th><td><span class="badge badge-info">{{ $tender->procurement_method }}</span></td></tr>
                    <tr><th class="">Tender Package No</th><td>{{ $tender->tender_package_no }}</td></tr>
                    <tr><th class="">Tender Package Name</th><td>{{ $tender->tender_package_name }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="bg-dark text-white">
                    <tr>
                        <th width="15%">Item Code</th>
                        <th>Description of Items</th>
                        <th>Unit</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Rate (BDT)</th>
                        <th class="text-right">Total Amount (BDT)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $grandTotal = 0; @endphp
                    @foreach($tender->items as $item)
                        @php
                            $lineTotal = $item->item_quantity * $item->item_rate;
                            $grandTotal += $lineTotal;
                        @endphp
                        <tr>
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->item_unit }}</td>
                            <td class="text-right">{{ number_format($item->item_quantity, 2) }}</td>
                            <td class="text-right">{{ number_format($item->item_rate, 2) }}</td>
                            <td class="text-right"><strong>{{ number_format($lineTotal, 2) }}</strong></td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="bg-primary">
                        <th colspan="5" class="text-right">Total Estimated Amount:</th>
                        <th class="text-right text-white" style="font-size: 1.1rem;">
                            {{ number_format($grandTotal, 2) }}
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

@endsection
