<div class="btn-group">
    @can($viewGate)
        <a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.show', $row->id) }}">
            View Details
        </a>
    @endcan

    @can($itemGate)
        <a class="btn btn-xs btn-info" href="{{ route('admin.tender-item.index', ['tender_id' => $row->id]) }}">
            Items
        </a>
    @endcan
</div>
