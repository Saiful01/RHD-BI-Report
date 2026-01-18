<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use App\Models\TenderDivision;
use App\Models\TenderItem;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TenderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = TenderItem::with(['tender', 'division'])->select('tender_items.*');

            // Tender ID Filter
            if ($request->filled('tender_id_filter')) {
                $query->whereHas('tender', function ($q) use ($request) {
                    $q->where('tenderid', $request->tender_id_filter);
                });
            }

            // Division Filter
            if ($request->filled('division_id')) {
                $query->where('division_id', $request->division_id);
            }


            if ($request->filled('supplier_name')) {
                $query->whereHas('tender', function ($q) use ($request) {

                    $q->where('supplier_name', $request->supplier_name);
                });
            }


            if ($request->filled('item_code')) {
                $query->where('item_code', $request->item_code);
            }


            if ($request->filled('item_name')) {
                $query->where('item_name', $request->item_name);
            }

            return DataTables::of($query)
                ->addColumn('placeholder', '&nbsp;')
                ->addColumn('tender_id_display', fn($row) => $row->tender->tenderid ?? 'N/A')
                ->addColumn('division_name', fn($row) => $row->division->division ?? 'N/A')
                ->addColumn('supplier', fn($row) => $row->tender->supplier_name ?? 'N/A')
                ->editColumn('item_quantity', fn($row) => number_format($row->item_quantity, 2))
                ->editColumn('item_rate', fn($row) => number_format($row->item_rate, 2))
                ->addColumn('total_amount', function ($row) {
                    return number_format($row->item_quantity * $row->item_rate, 2);
                })
                ->rawColumns(['placeholder'])
                ->make(true);
        }

        $divisions = TenderDivision::pluck('division', 'id');
        return view('admin.tenderItems.index', compact('divisions'));
    }


    public function tenderSearch(Request $request)
    {
        $search = $request->get('q');

        return Tender::where('tenderid', 'LIKE', "%$search%")
            ->limit(15)
            ->get([
                'tenderid as id',
                'tenderid as text'
            ]);
    }


    public function supplierSearch(Request $request)
    {
        $search = $request->get('q');
        return Tender::where('supplier_name', 'LIKE', "%$search%")
            ->whereNotNull('supplier_name')
            ->distinct()
            ->limit(15)
            ->get(['supplier_name as id', 'supplier_name as text']);
    }


    public function itemCodeSearch(Request $request)
    {
        $search = $request->get('q');
        return TenderItem::where('item_code', 'LIKE', "%$search%")
            ->distinct()
            ->limit(15)
            ->get(['item_code as id', 'item_code as text']);
    }


    public function itemNameSearch(Request $request)
    {
        $search = $request->get('q');
        return TenderItem::where('item_name', 'LIKE', "%$search%")
            ->distinct()
            ->limit(15)
            ->get(['item_name as id', 'item_name as text']);
    }


    public function summeryReport(Request $request)
    {
        // =========================
        // AJAX REQUEST FOR DATA
        // =========================
        if ($request->ajax()) {
            $query = TenderItem::with(['tender', 'division'])
                ->whereHas('division')
                ->select('tender_items.*');


            // Tender ID Filter
            if ($request->filled('tender_id_filter')) {
                $query->whereHas('tender', fn($q) => $q->where('tenderid', $request->tender_id_filter));
            }

            // Division Filter (multiple)
            if ($request->filled('division_id')) {
                $query->whereIn('division_id', (array)$request->division_id);
            }

            // Ministry Filter
            if ($request->filled('ministry')) {
                $query->whereHas('tender', fn($q) => $q->whereIn('ministry_division', (array)$request->ministry));
            }

            // District Filter
            if ($request->filled('district')) {
                $query->whereHas('tender', fn($q) => $q->whereIn('procuring_entity_district', (array)$request->district));
            }

            // Supplier Filter
            if ($request->filled('supplier_name')) {
                $query->whereHas('tender', fn($q) => $q->whereIn('supplier_name', (array)$request->supplier_name));
            }

            // Item Code Filter (multiple)
            if ($request->filled('item_code')) {
                $query->whereIn('item_code', (array)$request->item_code);
            }

            // Item Name Filter (multiple)
            if ($request->filled('item_name')) {
                $query->whereIn('item_name', (array)$request->item_name);
            }

            $items = $query->get();

            // Group by division
            $grouped = $items->groupBy(fn($row) => $row->division->division ?? 'N/A');

            // Prepare summary
            $summary = $grouped->mapWithKeys(function($items, $division) {
                return [$division => $items->count()];
            });

            $totalItems = $items->count();

            return response()->json([
                'grouped' => $grouped,
                'summary' => $summary,
                'totalItems' => $totalItems
            ]);
        }

        // =========================
        // LOAD FILTER DATA
        // =========================
        $divisions = TenderDivision::pluck('division', 'id');
        $ministries = Tender::distinct()->pluck('ministry_division')->filter()->toArray();
        $districts = Tender::distinct()->pluck('procuring_entity_district')->filter()->toArray();
        $tenderIds = Tender::distinct()->pluck('tenderid')->filter()->toArray();
        $suppliers = Tender::distinct()->pluck('supplier_name')->filter()->toArray();
        $itemCodes = TenderItem::distinct()->pluck('item_code')->filter()->toArray();
        $itemNames = TenderItem::distinct()->pluck('item_name')->filter()->toArray();

        return view('admin.tenderItems.summery_report', compact(
            'divisions','ministries','districts','tenderIds','suppliers','itemCodes','itemNames'
        ));
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
    public function show(TenderItem $tenderItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TenderItem $tenderItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TenderItem $tenderItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TenderItem $tenderItem)
    {
        //
    }
}
