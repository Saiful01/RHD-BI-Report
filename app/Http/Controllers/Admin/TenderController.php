<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use App\Models\TenderDivision;
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

            $perPage = $request->get('per_page', 12);
            return $query->orderBy('date_notification_award', 'desc')->paginate($perPage);
        }

        // Cache dropdown values for 1 hour to avoid slow queries on every page load
        $ministries = cache()->remember('tender_ministries', 3600, function () {
            return Tender::distinct()->pluck('ministry_division')->filter()->sort()->values()->toArray();
        });
        $districts = cache()->remember('tender_districts', 3600, function () {
            return Tender::distinct()->pluck('procuring_entity_district')->filter()->sort()->values()->toArray();
        });
        $methods = cache()->remember('tender_methods', 3600, function () {
            return Tender::distinct()->pluck('procurement_method')->filter()->sort()->values()->toArray();
        });

        // Suppliers are loaded via AJAX to avoid slow initial page load
        // Get divisions for the Tender Items tab
        $divisions = TenderDivision::pluck('division', 'id');

        return view('admin.tenders.index', compact('ministries', 'methods', 'districts', 'divisions'));
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

    public function supplierSearch(Request $request)
    {
        $search = $request->q;
        $suppliers = Tender::select('supplier_name')
            ->where('supplier_name', 'LIKE', "%$search%")
            ->distinct()
            ->limit(15)
            ->pluck('supplier_name');

        $response = [];
        foreach ($suppliers as $supplier) {
            if ($supplier) {
                $response[] = ['id' => $supplier, 'text' => $supplier];
            }
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
    public function show(Tender $tender, Request $request)
    {
        $tender->load('items');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'tender' => $tender
            ]);
        }

        // Redirect to index since we use modals now
        return redirect()->route('admin.tender.index');
    }

    public function viewItems(Tender $tender, Request $request)
    {
        $tender->load('items');

        $totalAmount = $tender->items->sum(function ($item) {
            return $item->item_quantity * $item->item_rate;
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'tender' => $tender,
                'items' => $tender->items,
                'totalAmount' => $totalAmount
            ]);
        }

        // Redirect to index since we use modals now
        return redirect()->route('admin.tender.index');
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
