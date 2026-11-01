@if(session('success'))
    <div class="mb-5 flex items-center p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200" role="alert">
        <svg class="shrink-0 inline w-5 h-5 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-5 flex items-center p-4 text-sm text-rose-800 rounded-xl bg-rose-50 border border-rose-200" role="alert">
        <svg class="shrink-0 inline w-5 h-5 mr-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
@endif

@if(session('status'))
    <div class="mb-5 flex items-center p-4 text-sm text-sky-800 rounded-xl bg-sky-50 border border-sky-200" role="alert">
        <svg class="shrink-0 inline w-5 h-5 mr-3 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-medium">{{ session('status') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="mb-5 p-4 text-sm text-rose-800 rounded-xl bg-rose-50 border border-rose-200" role="alert">
        <div class="flex items-center mb-1">
            <svg class="shrink-0 inline w-5 h-5 mr-2 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span class="font-bold">Terjadi beberapa kesalahan input:</span>
        </div>
        <ul class="list-disc list-inside ml-5 mt-1 space-y-0.5 text-xs text-rose-700">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
