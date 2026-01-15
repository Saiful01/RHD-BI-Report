<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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


            if ($request->filled('tender_id_filter')) {
                $query->whereHas('tender', function($q) use ($request) {
                    $q->where('tenderid', $request->tender_id_filter);
                });
            }

            return DataTables::of($query)
                ->addColumn('placeholder', '&nbsp;')
                ->addColumn('tender_id_display', fn($row) => $row->tender->tenderid ?? 'N/A')
                ->addColumn('division_name', fn($row) => $row->division->division ?? 'N/A')
                ->addColumn('supplier', fn($row) => $row->tender->supplier_name ?? 'N/A')
                ->editColumn('item_quantity', fn($row) => number_format($row->item_quantity, 2))
                ->editColumn('item_rate', fn($row) => number_format($row->item_rate, 2))
                ->addColumn('total_amount', fn($row) => number_format($row->item_quantity * $row->item_rate, 2))
                ->rawColumns(['placeholder'])
                ->make(true);
        }

        return view('admin.tenderItems.index');
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
