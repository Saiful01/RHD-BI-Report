@extends('layouts.admin')
@section('content')

    <div class="card">
        <div class="card-header">
            <strong>Tender Item List  [ Tender ID: {{ $tender->tenderid }}] </strong>
            <a class="btn btn-secondary btn-sm float-right" href="{{ route('admin.tender.index') }}">
                Back to List
            </a>
        </div>

        <div class="card-body">

            <h5 class="text-primary border-bottom pb-2 mb-3">
                <i class="fas fa-info-circle"></i> Basic Information
            </h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <tbody>
                    <tr><th class="" width="30%">Tender ID</th><td>{{ $tender->tenderid }}</td></tr>
                    <tr><th class="">Ministry / Division</th><td>{{ $tender->ministry_division }}</td></tr>
                    <tr><th class="">Agency</th><td>{{ $tender->agency }}</td></tr>
                    <tr><th class="">Project Name</th><td>{{ $tender->project_name ?? 'N/A' }}</td></tr>
                    </tbody>
                </table>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3">
                <i class="fas fa-box"></i> Package & Procurement Method
            </h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <tbody>
                    <tr><th class="" width="30%">Procuring Entity Name</th><td>{{ $tender->procuring_entity_name }} ({{ $tender->procuring_entity_code }})</td></tr>
                    <tr><th class="">Procuring Entity District</th><td>{{ $tender->procuring_entity_district }}</td></tr>
                    <tr><th class="">Procurement Method</th><td><span class="badge badge-info">{{ $tender->procurement_method }}</span></td></tr>
                    <tr><th class="">Tender Package No</th><td>{{ $tender->tender_package_no }}</td></tr>
                    <tr><th class="">Tender Package Name</th><td>{{ $tender->tender_package_name }}</td></tr>
                    </tbody>
                </table>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3">
                <i class="fas fa-handshake"></i> Contract & Award Information
            </h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <tbody>
                    <tr><th class="" width="30%">Supplier Name</th><td>{{ $tender->supplier_name }}</td></tr>
                    <tr><th class="">Contract Value (BDT)</th><td class="font-weight-bold text-success">{{ number_format($tender->contract_value, 2) }}</td></tr>
                    <tr><th class="">Date of Notification (NOA)</th><td>{{ $tender->date_notification_award ? $tender->date_notification_award->format('d-M-Y') : 'N/A' }}</td></tr>
                    <tr><th class="">Date of Contract Signing</th><td>{{ $tender->date_contract_signing ? $tender->date_contract_signing->format('d-M-Y') : 'N/A' }}</td></tr>
                    <tr><th class="">Proposed Completion Date</th><td>{{ $tender->proposed_date_contract_completion ? $tender->proposed_date_contract_completion->format('d-M-Y') : 'N/A' }}</td></tr>
                    </tbody>
                </table>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3">
                <i class="fas fa-list-alt"></i> Other Details
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                    <tr><th class="" width="30%">Supplier Location</th><td>{{ $tender->supplier_location }}</td></tr>
                    <tr><th class="">Budget Source</th><td>{{ $tender->budget_source_funds }}</td></tr>
                    </tbody>
                </table>
            </div>

          {{--  <div class="mt-4 py-3  text-center border">
                <p class="mb-2 text-muted">To see the detailed bill of quantities and items:</p>
                <a href="{{ route('admin.tender-item.index', ['tender_id' => $tender->id]) }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-external-link-alt"></i> View All Tender Items
                </a>
            </div>--}}
        </div>
    </div>

@endsection
