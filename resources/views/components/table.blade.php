<div class="overflow-x-auto w-full rounded-lg border border-slate-200">
    <table {{ $attributes->merge(['class' => 'w-full text-left text-sm text-slate-600']) }}>
        @if(isset($header))
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                {{ $header }}
            </thead>
        @endif
        <tbody class="divide-y divide-slate-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
