<div class="action-buttons">
    @can($viewGate)
        <a class="fluent-btn fluent-btn-sm fluent-btn-primary" href="{{ route('admin.' . $crudRoutePart . '.show', $row->id) }}" title="{{ trans('global.view') }}">
            <i class="ri-eye-line"></i>
        </a>
    @endcan
    @can($editGate)
        <a class="fluent-btn fluent-btn-sm fluent-btn-secondary" href="{{ route('admin.' . $crudRoutePart . '.edit', $row->id) }}" title="{{ trans('global.edit') }}">
            <i class="ri-pencil-line"></i>
        </a>
    @endcan
    @can($deleteGate)
        <form action="{{ route('admin.' . $crudRoutePart . '.destroy', $row->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
            @method('DELETE')
            @csrf
            <button type="submit" class="fluent-btn fluent-btn-sm fluent-btn-danger" title="{{ trans('global.delete') }}">
                <i class="ri-delete-bin-line"></i>
            </button>
        </form>
    @endcan
</div>
