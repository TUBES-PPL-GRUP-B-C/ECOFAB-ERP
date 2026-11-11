<?php

namespace App\Http\Controllers\Bursa;

use App\Http\Controllers\Controller;
use App\Models\WasteListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicWasteCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);
        $search = trim($filters['cari'] ?? '');
        $category = $filters['category'] ?? '';

        // Category options must not reveal draft or unavailable listings.
        $categories = $this->publicListings()->select('category')->distinct()->orderBy('category')->pluck('category');
        $query = $this->publicListings();

        // The shared search scope treats "0" as empty; keep it searchable here.
        if ($search === '0') {
            $query->where('category', 'LIKE', '%0%');
        } else {
            $query->search($search, ['category']);
        }

        $listings = $query->filter(['category' => $category])->orderByDesc('id')->get();

        return view('public.katalog_limbah', compact('listings', 'categories', 'search', 'category'));
    }

    public function show(string $id): View
    {
        $listing = $this->publicListings()->findOrFail($id);

        return view('public.katalog_detail', compact('listing'));
    }

    private function publicListings(): Builder
    {
        // FR-22's model/table and stock contract are not available yet.
        // Use only listing fields; never load the unresolved wasteRecord relation.
        return WasteListing::query()
            ->select(['id', 'category', 'price_per_kg', 'available_weight', 'photo_path'])
            ->where('status', 'published')
            ->where('available_weight', '>', 0);
    }
}
