<div class="space-y-1.5">
    <label for="publication_status" class="block text-xs font-semibold text-slate-700">Publikasi</label>
    <select id="publication_status" name="publication_status" required class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900" aria-describedby="publication_help">
        <option value="draft" @selected(old('publication_status', $listing->status ?? 'draft') === 'draft')>Draft — hanya internal</option>
        <option value="published" @selected(old('publication_status', $listing->status ?? 'draft') === 'published')>Published — tampil di katalog publik</option>
    </select>
    <p id="publication_help" class="text-xs text-slate-500">Listing published tampil di katalog jika stok masih tersedia dan sumber limbah layak digunakan. Harga per kg dan foto dapat dilihat pengunjung.</p>
    @error('publication_status') <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p> @enderror
</div>
