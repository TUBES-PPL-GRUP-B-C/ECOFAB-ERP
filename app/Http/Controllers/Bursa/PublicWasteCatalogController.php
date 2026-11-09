<?php

namespace App\Http\Controllers\Bursa;

use App\Http\Controllers\Controller;
use App\Models\WasteListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class PublicWasteCatalogController extends Controller
{
    public function index(): View
    {
        $listings = $this->publicListings()->orderByDesc('id')->get();

        return view('public.katalog_limbah', compact('listings'));
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
            ->select(['id', 'category', 'price_per_kg', 'available_weight'])
            ->where('status', 'published')
            ->where('available_weight', '>', 0);
    }
}
