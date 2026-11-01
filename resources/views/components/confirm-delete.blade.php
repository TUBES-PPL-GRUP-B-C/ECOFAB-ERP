@props([
    'action',
    'itemName' => 'item ini',
    'buttonClass' => 'text-xs text-rose-600 hover:text-rose-800 font-medium',
    'buttonText' => 'Hapus',
])

<form action="{{ $action }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus {{ addslashes($itemName) }}? Tindakan ini tidak dapat dibatalkan.');">
    @csrf
    @method('DELETE')
    <button type="submit" class="{{ $buttonClass }}">
        {{ $buttonText }}
    </button>
</form>
