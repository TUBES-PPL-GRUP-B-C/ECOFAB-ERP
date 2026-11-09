<?php

namespace Tests\Feature;

use App\Models\WasteListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $publicFields = ['id', 'category', 'price_per_kg', 'available_weight'];

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
