@props(['paginator'])

@if($paginator->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
        <div>
            Menampilkan
            <span class="font-semibold text-slate-700">{{ $paginator->firstItem() ?? 0 }}</span>
            sampai
            <span class="font-semibold text-slate-700">{{ $paginator->lastItem() ?? 0 }}</span>
            dari
            <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
            data
        </div>

        <div>
            {{ $paginator->links() }}
        </div>
    </div>
@endif
