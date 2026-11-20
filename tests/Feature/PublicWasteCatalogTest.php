<?php

namespace Tests\Feature;

use App\Models\WasteListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicWasteCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->withoutVite();
    }

    public function test_guest_can_open_an_empty_catalog_without_fr22(): void
    {
        $this->get(route('public.bursa.index'))->assertOk()
            ->assertViewIs('public.katalog_limbah')
            ->assertViewHas('listings', fn ($listings) => $listings->isEmpty())
            ->assertSee('Belum ada limbah tersedia')
            ->assertSee('Masuk Portal Internal')
            ->assertDontSee('id="sidebar"', false);

        $this->assertGuest();
    }

    public function test_missing_and_invalid_listing_ids_return_not_found(): void
    {
        foreach (['999', 'invalid', '-1', '1.5'] as $id) {
            $this->get(route('public.bursa.show', $id))->assertNotFound();
        }

        $this->assertGuest();
    }

    public function test_new_listings_default_to_draft_and_are_hidden_from_guests(): void
    {
        $listing = WasteListing::create([
            'waste_record_id' => 999,
            'category' => 'Draft baru',
            'price_per_kg' => '1250.50',
            'available_weight' => 5,
        ]);

        $this->assertSame('draft', $listing->fresh()->status);
        $this->get('/bursa/katalog')->assertOk()
            ->assertViewHas('listings', fn ($listings) => $listings->isEmpty())
            ->assertDontSee($listing->category);
        $this->get("/bursa/katalog/{$listing->id}")->assertNotFound();
        $this->assertGuest();
    }

    public function test_guest_sees_only_published_listings_with_positive_stock(): void
    {
        $available = $this->listing('published', '0.001');
        $draft = $this->listing('draft', 5);
        $withdrawn = $this->listing('withdrawn', 5);
        $unknown = $this->listing('unknown', 5);
        $empty = $this->listing('published', 0);
        $negative = $this->listing('published', -1);

        $this->get('/bursa/katalog?status=draft&available_weight=0')->assertOk()
            ->assertViewHas('listings', fn ($listings) => $listings->modelKeys() === [$available->id])
            ->assertSee($available->category)
            ->assertSee('0.001')->assertSee('1250.50')
            ->assertDontSee($draft->category)
            ->assertDontSee($withdrawn->category)
            ->assertDontSee($unknown->category)
            ->assertDontSee($empty->category)
            ->assertDontSee($negative->category);

        $this->get(route('public.bursa.show', $available->id))->assertOk()
            ->assertViewIs('public.katalog_detail')
            ->assertSee($available->category)->assertSee('1250.50')->assertSee('0.001');

        foreach ([$draft, $withdrawn, $unknown, $empty, $negative] as $hidden) {
            $this->get(route('public.bursa.show', $hidden->id))->assertNotFound();
        }

        $this->assertGuest();
    }

    public function test_catalog_payload_excludes_internal_fields_and_escapes_category(): void
    {
        $listing = $this->listing('published', 5);
        $listing->update(['category' => '<script>alert("test")</script>']);
        $publicFields = ['id', 'category', 'price_per_kg', 'available_weight', 'photo_path'];

        $this->get('/bursa/katalog')->assertOk()
            ->assertViewHas('listings', function ($listings) use ($publicFields) {
                $this->assertSame($publicFields, array_keys($listings->first()->getAttributes()));

                return true;
            })
            ->assertSee($listing->category)
            ->assertDontSee($listing->category, false)
            ->assertDontSee('waste_record_id');

        $this->get("/bursa/katalog/{$listing->id}")->assertOk()
            ->assertViewHas('listing', function ($item) use ($publicFields) {
                $this->assertSame($publicFields, array_keys($item->getAttributes()));

                return true;
            })
            ->assertSee($listing->category)
            ->assertDontSee($listing->category, false)
            ->assertDontSee('waste_record_id');
    }

    public function test_catalog_does_not_mutate_listing_or_support_write_requests(): void
    {
        $listing = $this->listing('published', 5);
        $before = $listing->fresh()->getAttributes();

        $this->get('/bursa/katalog')->assertOk();
        $this->get("/bursa/katalog/{$listing->id}")->assertOk();
        foreach (['post', 'put', 'delete'] as $method) {
            $this->{$method}("/bursa/katalog/{$listing->id}")->assertStatus(405);
        }

        $this->assertSame($before, $listing->fresh()->getAttributes());
        $this->assertDatabaseCount('waste_listings', 1);
    }

    public function test_direct_detail_access_stops_when_stock_or_publication_changes(): void
    {
        $listing = $this->listing('published', 5);
        $url = route('public.bursa.show', $listing->id);
        $this->get($url)->assertOk();

        $listing->update(['available_weight' => 0]);
        $this->get($url)->assertNotFound();

        $listing->available_weight = 5;
        $listing->status = 'draft';
        $listing->save();
        $this->get($url)->assertNotFound();
    }

    public function test_search_and_exact_category_filter_can_be_combined_by_guests(): void
    {
        $wood = $this->listing('published', 5);
        $wood->update(['category' => 'Potongan kayu']);
        $dust = $this->listing('published', 5);
        $dust->update(['category' => 'Serbuk kayu']);
        $rattan = $this->listing('published', 5);
        $rattan->update(['category' => 'Rotan']);

        $this->get('/bursa/katalog?cari=%20kayu%20')->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->modelKeys() === [$dust->id, $wood->id])
            ->assertSee('value="kayu"', false);
        $this->get('/bursa/katalog?'.http_build_query(['cari' => 'kayu', 'category' => 'Potongan kayu']))
            ->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->modelKeys() === [$wood->id])
            ->assertSee('<option value="Potongan kayu" selected', false)
            ->assertSee('Reset pencarian');
        $this->get('/bursa/katalog?category=kayu')->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->isEmpty())
            ->assertSee('Tidak ada limbah yang cocok');
        $this->get('/bursa/katalog?category=Rotan')->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->modelKeys() === [$rattan->id]);
        $this->get('/bursa/katalog?cari=&category=')->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->count() === 3)
            ->assertDontSee('Reset pencarian');
        $this->assertGuest();
    }

    public function test_category_options_are_unique_and_only_use_public_available_listings(): void
    {
        foreach ([['published', 5, 'Kayu'], ['published', 2, 'Kayu'], ['published', 1, 'Rotan'],
            ['draft', 5, 'Kategori rahasia'], ['withdrawn', 5, 'Ditarik'],
            ['published', 0, 'Stok kosong'], ['published', -1, 'Stok negatif']] as [$status, $weight, $category]) {
            $this->listing($status, $weight)->update(['category' => $category]);
        }

        $this->get('/bursa/katalog?cari=Rotan')->assertOk()
            ->assertViewHas('categories', fn ($items) => $items->all() === ['Kayu', 'Rotan'])
            ->assertViewHas('listings', fn ($items) => $items->count() === 1);
        $this->get('/bursa/katalog?'.http_build_query(['cari' => 'Kategori', 'category' => 'Kategori rahasia']))
            ->assertOk()->assertViewHas('listings', fn ($items) => $items->isEmpty());
        $this->get('/bursa/katalog?cari=%25')->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->count() === 3)
            ->assertDontSee('Kategori rahasia')->assertDontSee('Stok kosong');
    }

    public function test_search_for_zero_is_not_treated_as_an_empty_search(): void
    {
        $match = $this->listing('published', 5);
        $match->update(['category' => 'Kayu 0']);
        $this->listing('published', 5)->update(['category' => 'Rotan']);

        $this->get('/bursa/katalog?cari=0')->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->modelKeys() === [$match->id]);
    }

    public function test_search_input_is_validated_and_escaped_without_sql_injection(): void
    {
        $this->listing('published', 5);
        foreach (['cari', 'category'] as $field) {
            foreach ([['invalid'], str_repeat('a', 256)] as $invalid) {
                $this->getJson('/bursa/katalog?'.http_build_query([$field => $invalid]))
                    ->assertUnprocessable()->assertJsonValidationErrors($field);
            }
        }

        $this->get('/bursa/katalog?'.http_build_query(['cari' => "' OR 1=1 --"]))
            ->assertOk()->assertViewHas('listings', fn ($items) => $items->isEmpty());
        $script = '<script>alert("search")</script>';
        $this->get('/bursa/katalog?'.http_build_query(['cari' => $script]))
            ->assertOk()->assertSee($script)->assertDontSee($script, false)
            ->assertSee('Tidak ada limbah yang cocok');
    }

    public function test_detail_displays_public_photo_and_fallback_without_internal_order_link(): void
    {
        $listing = $this->listing('published', 5);
        $url = route('public.bursa.show', $listing->id);
        $this->get($url)->assertOk()->assertSee('Foto limbah belum tersedia.')
            ->assertSee(route('public.bursa.index'), false)
            ->assertDontSee(route('bursa.order.index'), false);

        $listing->update(['photo_path' => 'waste-listings/sample.jpg']);
        $this->get($url)->assertOk()
            ->assertSee(Storage::disk('public')->url($listing->photo_path), false)
            ->assertSee('Foto limbah '.$listing->category)
            ->assertDontSee('Foto limbah belum tersedia.')
            ->assertDontSee('waste_record_id')
            ->assertDontSee(route('bursa.order.index'), false);
    }

    public function test_guest_can_paginate_only_available_public_listings_without_duplicates(): void
    {
        $ids = [];
        for ($index = 0; $index < 25; $index++) {
            $ids[] = $this->listing('published', 5)->id;
        }
        foreach ([['draft', 5], ['withdrawn', 5], ['published', 0], ['published', -1]] as [$status, $weight]) {
            $this->listing($status, $weight);
        }

        $expected = array_reverse($ids);
        foreach ([1, 2, 3] as $page) {
            $this->get('/bursa/katalog?page='.$page)->assertOk()
                ->assertViewHas('listings', function ($items) use ($expected, $page) {
                    $this->assertInstanceOf(LengthAwarePaginator::class, $items);
                    $this->assertSame(25, $items->total());
                    $this->assertSame(12, $items->perPage());
                    $this->assertSame(3, $items->lastPage());
                    $this->assertSame($page, $items->currentPage());
                    $this->assertSame(array_slice($expected, ($page - 1) * 12, 12), $items->modelKeys());
                    foreach ($items as $item) {
                        $this->assertSame(
                            ['id', 'category', 'price_per_kg', 'available_weight', 'photo_path'],
                            array_keys($item->getAttributes()),
                        );
                    }

                    return true;
                })
                ->assertSee('Menampilkan')->assertDontSee('Tidak ada limbah yang cocok');
        }
        $this->assertGuest();
    }

    public function test_pagination_links_keep_only_validated_search_and_category_and_filters_reset_page(): void
    {
        $ids = [];
        for ($index = 0; $index < 13; $index++) {
            $listing = $this->listing('published', 5);
            $listing->update(['category' => 'Potongan kayu & rotan']);
            $ids[] = $listing->id;
        }
        $this->listing('published', 5)->update(['category' => 'Serbuk kayu']);
        $this->listing('draft', 5)->update(['category' => 'Potongan kayu & rotan']);
        $filters = ['cari' => ' kayu ', 'category' => 'Potongan kayu & rotan', 'status' => 'draft', 'per_page' => 100];

        $response = $this->get('/bursa/katalog?'.http_build_query($filters))->assertOk();
        $paginator = $response->viewData('listings');
        parse_str(parse_url($paginator->nextPageUrl(), PHP_URL_QUERY), $nextQuery);
        $this->assertSame(['cari' => 'kayu', 'category' => 'Potongan kayu & rotan', 'page' => '2'], $nextQuery);
        $response->assertSee($paginator->nextPageUrl())
            ->assertViewHas('listings', fn ($items) => $items->total() === 13 && $items->count() === 12)
            ->assertDontSee('name="page"', false);

        $this->get($paginator->nextPageUrl())->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->modelKeys() === [$ids[0]] && $items->total() === 13)
            ->assertSee('value="kayu"', false)
            ->assertSee('<option value="Potongan kayu &amp; rotan" selected', false)
            ->assertSee('Reset pencarian');
        $this->get('/bursa/katalog?category=Serbuk+kayu')->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->currentPage() === 1 && $items->total() === 1)
            ->assertDontSee('Menampilkan');
        $this->get(route('public.bursa.index'))->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->currentPage() === 1 && $items->total() === 14);
        $this->assertGuest();
    }

    public function test_page_input_rejects_invalid_values(): void
    {
        foreach ([['invalid'], 'abc', 0, -1, '1.5', '2147483648'] as $page) {
            $this->getJson('/bursa/katalog?'.http_build_query(['page' => $page]))
                ->assertUnprocessable()->assertJsonValidationErrors('page');
        }
        $this->get('/bursa/katalog?page=')->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->currentPage() === 1);
    }

    public function test_out_of_range_page_has_a_recovery_link_preserving_filters(): void
    {
        $this->listing('published', 5)->update(['category' => 'Kayu']);
        $response = $this->get('/bursa/katalog?cari=Kayu&category=Kayu&page=99')->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->isEmpty() && $items->total() === 1)
            ->assertSee('Halaman katalog tidak tersedia')
            ->assertSee('Ke halaman pertama')
            ->assertDontSee('Belum ada limbah tersedia')
            ->assertDontSee('Tidak ada limbah yang cocok');
        $this->get($response->viewData('listings')->url(1))->assertOk()
            ->assertViewHas('listings', fn ($items) => $items->currentPage() === 1 && $items->total() === 1)
            ->assertDontSee('Halaman katalog tidak tersedia');
    }

    private function listing(string $status, string|int $weight): WasteListing
    {
        $listing = WasteListing::create([
            'waste_record_id' => 999,
            'category' => 'Kategori '.WasteListing::count(),
            'price_per_kg' => '1250.50',
            'available_weight' => $weight,
        ]);
        // Status is intentionally not mass assignable in FR-23.
        $listing->status = $status;
        $listing->save();

        return $listing;
    }
}
