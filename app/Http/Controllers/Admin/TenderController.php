<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TenderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Tender::query();

            if ($request->filled('tenderid')) {
                $query->where('tenderid', $request->tenderid);
            }
            if ($request->filled('ministry_division')) {
                $query->where('ministry_division', $request->ministry_division);
            }
            if ($request->filled('supplier_name')) {
                $query->where('supplier_name', $request->supplier_name);
            }
            if ($request->filled('procurement_method')) {
                $query->where('procurement_method', $request->procurement_method);
            }
            if ($request->filled('district')) {
                $query->where('procuring_entity_district', $request->district);
            }
            if ($request->filled('package_no')) {
                $query->where('tender_package_no', 'LIKE', '%' . $request->package_no . '%');
            }

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('date_notification_award', [$request->from_date, $request->to_date]);
            }

            return DataTables::of($query)
                ->addColumn('placeholder', '&nbsp;')
                ->editColumn('tender_package_no', function ($row) {
                    return $row->tender_package_no ?? '';
                })
                ->editColumn('date_notification_award', function ($row) {
                    return $row->date_notification_award ? $row->date_notification_award->format('d-M-Y') : '';
                })
                ->addColumn('entity_info', function ($row) {
                    return "Code: {$row->procuring_entity_code}<br>" .
                        "District: {$row->procuring_entity_district}<br>" .
                        "Method: <span class='badge badge-info'>{$row->procurement_method}</span>";
                })
                ->addColumn('value_cr', function ($row) {
                    $cr = $row->contract_value / 10000000;
                    return number_format($cr, 2) . ' Cr';
                })
                ->addColumn('actions', function ($row) {
                    $viewUrl = route('admin.tender.show', $row->id);
                    $itemUrl = route('admin.tender.viewItems', $row->id);

                    return '<div class="">
            <a class="btn btn-xs btn-primary" href="' . $viewUrl . '">View</a>
            <a class="btn btn-xs btn-info" href="' . $itemUrl . '">Items</a>
        </div>';
                })
                ->rawColumns(['actions', 'placeholder', 'entity_info'])
                ->make(true);
        }


        $ministries = Tender::distinct()->pluck('ministry_division')->filter()->toArray();
        $methods = Tender::distinct()->pluck('procurement_method')->filter()->toArray();
        $districts = Tender::distinct()->pluck('procuring_entity_district')->filter()->toArray();

        $tenderIds = Tender::distinct()->pluck('tenderid')->filter()->toArray();
        $suppliers = Tender::distinct()->pluck('supplier_name')->filter()->toArray();

        return view('admin.tenders.index', compact('ministries', 'methods', 'districts', 'tenderIds', 'suppliers'));
    }


    public function selectSearch(Request $request)
    {
        $search = $request->q;
        $tenders = Tender::select('tenderid')
            ->where('tenderid', 'LIKE', "%$search%")
            ->distinct()
            ->limit(10)
            ->get();

        $response = [];
        foreach ($tenders as $tender) {
            $response[] = ['id' => $tender->tenderid, 'text' => $tender->tenderid];
        }
        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tender $tender)
    {
        $tender->load('items');

        return view('admin.tenders.show', compact('tender'));
    }

    public function viewItems(Tender $tender)
    {

        $tender->load('items');


        $totalAmount = $tender->items->sum(function ($item) {
            return $item->item_quantity * $item->item_rate;
        });

        return view('admin.tenders.item', compact('tender', 'totalAmount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tender $tender)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tender $tender)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tender $tender)
    {
        //
    }
}
