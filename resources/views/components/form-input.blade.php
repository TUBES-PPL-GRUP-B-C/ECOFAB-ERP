@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'help' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all outline-hidden bg-white text-slate-900 placeholder:text-slate-400 @error($name) border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
        >
    </div>

    @if($help)
        <p class="text-xs text-slate-500">{{ $help }}</p>
    @endif

    @error($name)
        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
    @enderror
</div>
