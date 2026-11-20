<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteListing;
use App\Models\WasteRecord;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WasteListingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_listing_routes_require_authentication_and_correct_permissions(): void
    {
        $this->get('/bursa/listing')->assertRedirect('/login');
        $this->get('/bursa/listing/create')->assertRedirect('/login');
        $this->get('/bursa/listing/1')->assertRedirect('/login');
        $this->get('/bursa/listing/1/edit')->assertRedirect('/login');
        $this->post('/bursa/listing', [])->assertRedirect('/login');
        $this->put('/bursa/listing/1', [])->assertRedirect('/login');
        $this->delete('/bursa/listing/1')->assertRedirect('/login');

        $user = User::factory()->create();
        $user->assignRole('produksi');
        $this->actingAs($user)->get('/bursa/listing')->assertForbidden();
        $this->get('/bursa/listing/create')->assertForbidden();
        $this->post('/bursa/listing', [])->assertForbidden();

        $user->syncRoles(['penjualan']);
        $this->get('/bursa/listing')->assertOk();
        $this->get('/bursa/listing/create')->assertOk()
            ->assertSee('Data limbah produksi FR-22 belum tersedia.');
    }

    public function test_missing_fr22_table_blocks_creation_without_a_database_error(): void
    {
        // Exercise the absent dependency explicitly now that Rizal supplies its migration.
        Schema::drop('waste_records');

        $this->actingAs($this->commercialUser())
            ->post('/bursa/listing', $this->attributes())
            ->assertStatus(503);
        $this->assertDatabaseCount('waste_listings', 0);
    }

    public function test_shared_roles_control_access_to_all_internal_listing_routes(): void
    {
        $this->createWasteRecordFixture();
        $listing = WasteListing::create($this->attributes());

        foreach (['admin', 'penjualan', 'hr', 'pembelian', 'gudang', 'produksi'] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);
            $this->actingAs($user);

            foreach (['/bursa/listing', '/bursa/listing/create', "/bursa/listing/{$listing->id}", "/bursa/listing/{$listing->id}/edit"] as $url) {
                $response = $this->get($url);
                if (in_array($role, ['admin', 'penjualan'], true)) {
                    $response->assertOk();
                } else {
                    $response->assertForbidden();
                }
            }

            if (! in_array($role, ['admin', 'penjualan'], true)) {
                $this->post('/bursa/listing', $this->attributes())->assertForbidden();
                $this->put("/bursa/listing/{$listing->id}", $this->attributes())->assertForbidden();
                $this->delete("/bursa/listing/{$listing->id}")->assertForbidden();
            }
        }

        $this->assertDatabaseHas('waste_listings', ['id' => $listing->id]);
    }

    public function test_index_uses_shared_layout_flash_and_pagination_without_duplicate_messages(): void
    {
        $this->createWasteRecordFixture();
        for ($number = 1; $number <= 11; $number++) {
            WasteListing::create(array_merge($this->attributes(), ['category' => "Kategori uji {$number}"]));
        }

        $message = 'Pesan sukses listing untuk audit';
        $response = $this->actingAs($this->commercialUser())
            ->withSession(['success' => $message])->get('/bursa/listing');
        $response->assertOk()->assertSee('id="sidebar"', false)
            ->assertSee('Menampilkan')->assertSee('sampai')->assertSee('dari')
            ->assertSee('page=2', false)
            ->assertViewHas('listings', fn ($listings) => $listings->count() === 10);
        $this->assertSame(1, substr_count($response->getContent(), $message));

        $this->get('/bursa/listing?page=2')->assertOk()
            ->assertSee('Kategori uji')
            ->assertViewHas('listings', fn ($listings) => $listings->count() === 1);
    }

    public function test_create_form_shows_available_reference_and_old_values_after_validation_failure(): void
    {
        $this->createWasteRecordFixture();
        $this->actingAs($this->commercialUser());

        $this->get('/bursa/listing/create')->assertOk()
            ->assertSee('action="'.route('bursa.listing.store').'"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('Limbah produksi #1');

        $this->from('/bursa/listing/create')->post('/bursa/listing', [
            'waste_record_id' => 1,
            'category' => 'Kayu uji',
            'price_per_kg' => -2,
            'available_weight' => 1.25,
        ])->assertRedirect('/bursa/listing/create')
            ->assertSessionHasErrors('price_per_kg');

        $this->get('/bursa/listing/create')->assertOk()
            ->assertSee('value="1" selected', false)
            ->assertSee('value="Kayu uji"', false)
            ->assertSee('value="-2"', false)
            ->assertSee('value="1.25"', false)
            ->assertSee('Terjadi beberapa kesalahan input:');
        $this->assertDatabaseCount('waste_listings', 0);
    }

    public function test_required_fields_and_decimal_precision_are_validated(): void
    {
        $this->createWasteRecordFixture();
        $this->actingAs($this->commercialUser());

        $this->post('/bursa/listing', [])->assertSessionHasErrors([
            'waste_record_id', 'category', 'price_per_kg', 'available_weight',
        ]);
        $this->post('/bursa/listing', array_merge($this->attributes(), [
            'price_per_kg' => '12.345',
            'available_weight' => '1.2345',
        ]))->assertSessionHasErrors(['price_per_kg', 'available_weight']);
        $this->assertDatabaseCount('waste_listings', 0);
    }

    public function test_photo_size_and_extension_are_validated_before_storage(): void
    {
        $this->createWasteRecordFixture();
        Storage::fake('public');
        $this->actingAs($this->commercialUser());

        $this->post('/bursa/listing', array_merge($this->attributes(), [
            'photo' => UploadedFile::fake()->image('limbah.jpg')->size(2049),
        ]))->assertSessionHasErrors('photo');
        $this->post('/bursa/listing', array_merge($this->attributes(), [
            'photo' => UploadedFile::fake()->image('limbah.txt'),
        ]))->assertSessionHasErrors('photo');

        $this->assertDatabaseCount('waste_listings', 0);
        $this->assertSame([], Storage::disk('public')->files('waste-listings'));
    }

    public function test_store_validates_reference_price_weight_and_photo_and_saves_draft(): void
    {
        $this->createWasteRecordFixture();
        Storage::fake('public');
        $this->actingAs($this->commercialUser());

        $this->post('/bursa/listing', array_merge($this->attributes(), [
            'price_per_kg' => -1,
            'available_weight' => 0,
            'photo' => UploadedFile::fake()->create('script.php', 1, 'application/x-php'),
        ]))->assertSessionHasErrors(['price_per_kg', 'available_weight', 'photo']);
        $this->assertDatabaseCount('waste_listings', 0);

        $this->post('/bursa/listing', array_merge($this->attributes(), [
            'waste_record_id' => 999,
        ]))->assertSessionHasErrors('waste_record_id');

        $this->post('/bursa/listing', array_merge($this->attributes(), [
            'photo' => UploadedFile::fake()->image('limbah.jpg'),
            'status' => 'published',
        ]))->assertRedirect('/bursa/listing');

        $listing = WasteListing::firstOrFail();
        $this->assertSame('draft', $listing->status);
        $this->assertSame(1, $listing->waste_record_id);
        Storage::disk('public')->assertExists($listing->photo_path);
    }

    public function test_update_and_delete_require_action_permissions(): void
    {
        $this->createWasteRecordFixture();
        $listing = WasteListing::create($this->attributes());
        $commercial = $this->commercialUser();
        // Use a user with only view permission to check action-specific access.
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('bursa.view');
        $this->actingAs($viewer)->put("/bursa/listing/{$listing->id}", $this->attributes())->assertForbidden();
        $this->delete("/bursa/listing/{$listing->id}")->assertForbidden();

        $this->actingAs($commercial)->put("/bursa/listing/{$listing->id}", array_merge($this->attributes(), [
            'category' => 'Rotan',
        ]))->assertRedirect('/bursa/listing');
        $this->assertDatabaseHas('waste_listings', ['id' => $listing->id, 'category' => 'Rotan']);
        $this->delete("/bursa/listing/{$listing->id}")->assertRedirect('/bursa/listing');
        $this->assertDatabaseCount('waste_listings', 0);
    }

    public function test_index_and_detail_display_saved_values_and_respect_view_permission(): void
    {
        $this->createWasteRecordFixture();
        $listing = WasteListing::create($this->attributes());
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('bursa.view');

        $this->actingAs($viewer)->get('/bursa/listing')->assertOk()
            ->assertSee('Kayu')->assertSee('5.000')
            ->assertSee(route('bursa.listing.show', $listing))
            ->assertDontSee(route('bursa.listing.edit', $listing));
        $this->get("/bursa/listing/{$listing->id}")->assertOk()
            ->assertSee('Kayu')->assertSee('1000.00')->assertSee('Limbah produksi');
        $this->get('/bursa/listing/999')->assertNotFound();
        $this->get("/bursa/listing/{$listing->id}/edit")->assertForbidden();
    }

    public function test_edit_shows_saved_values_and_old_input_and_updates_valid_data(): void
    {
        $this->createWasteRecordFixture();
        $listing = WasteListing::create($this->attributes());
        $this->actingAs($this->commercialUser());

        $this->get("/bursa/listing/{$listing->id}/edit")->assertOk()
            ->assertSee('value="1" selected', false)
            ->assertSee('value="Kayu"', false)
            ->assertSee('value="1000.00"', false)
            ->assertSee('value="5.000"', false);

        $this->from("/bursa/listing/{$listing->id}/edit")
            ->put("/bursa/listing/{$listing->id}", array_merge($this->attributes(), [
                'category' => 'Rotan', 'available_weight' => -1,
            ]))->assertRedirect("/bursa/listing/{$listing->id}/edit")
            ->assertSessionHasErrors('available_weight');
        $this->get("/bursa/listing/{$listing->id}/edit")->assertSee('value="Rotan"', false);
        $this->assertDatabaseHas('waste_listings', ['id' => $listing->id, 'category' => 'Kayu']);

        $this->put("/bursa/listing/{$listing->id}", array_merge($this->attributes(), [
            'category' => 'Rotan', 'price_per_kg' => '1250.50', 'available_weight' => '3.250',
            'status' => 'published',
        ]))->assertRedirect('/bursa/listing');
        $this->assertDatabaseHas('waste_listings', [
            'id' => $listing->id, 'category' => 'Rotan', 'price_per_kg' => '1250.50',
            'available_weight' => '3.250', 'status' => 'draft',
        ]);
    }

    public function test_replacing_photo_keeps_old_file_on_invalid_input_and_removes_it_on_success(): void
    {
        $this->createWasteRecordFixture();
        Storage::fake('public');
        $this->actingAs($this->commercialUser());
        $this->post('/bursa/listing', array_merge($this->attributes(), [
            'photo' => UploadedFile::fake()->image('first.jpg'),
        ]))->assertRedirect('/bursa/listing');
        $listing = WasteListing::firstOrFail();
        $oldPhoto = $listing->photo_path;

        $this->put("/bursa/listing/{$listing->id}", array_merge($this->attributes(), [
            'photo' => UploadedFile::fake()->image('too-big.png')->size(2049),
        ]))->assertSessionHasErrors('photo');
        Storage::disk('public')->assertExists($oldPhoto);
        $this->assertSame($oldPhoto, $listing->fresh()->photo_path);

        $this->put("/bursa/listing/{$listing->id}", array_merge($this->attributes(), [
            'photo' => UploadedFile::fake()->image('replacement.png'),
        ]))->assertRedirect('/bursa/listing');
        $newPhoto = $listing->fresh()->photo_path;
        $this->assertNotSame($oldPhoto, $newPhoto);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($newPhoto);
        $this->get("/bursa/listing/{$listing->id}")->assertSee(Storage::disk('public')->url($newPhoto));
    }

    public function test_delete_blocks_existing_orders_and_cleans_only_managed_photo(): void
    {
        $this->createWasteRecordFixture();
        Storage::fake('public');
        $this->actingAs($this->commercialUser());
        $this->post('/bursa/listing', array_merge($this->attributes(), [
            'photo' => UploadedFile::fake()->image('ordered.jpg'),
        ]))->assertRedirect('/bursa/listing');
        $listing = WasteListing::firstOrFail();
        $photo = $listing->photo_path;
        Schema::create('waste_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_listing_id');
        });
        \Illuminate\Support\Facades\DB::table('waste_orders')->insert(['waste_listing_id' => $listing->id]);

        $this->delete("/bursa/listing/{$listing->id}")->assertStatus(409);
        $this->assertDatabaseHas('waste_listings', ['id' => $listing->id]);
        Storage::disk('public')->assertExists($photo);

        \Illuminate\Support\Facades\DB::table('waste_orders')->delete();
        $this->delete("/bursa/listing/{$listing->id}")->assertRedirect('/bursa/listing');
        $this->assertDatabaseMissing('waste_listings', ['id' => $listing->id]);
        Storage::disk('public')->assertMissing($photo);
    }

    public function test_delete_refuses_unknown_order_schema(): void
    {
        $this->createWasteRecordFixture();
        $listing = WasteListing::create($this->attributes());
        Schema::create('waste_orders', function (Blueprint $table) {
            $table->id();
        });

        $this->actingAs($this->commercialUser())
            ->delete("/bursa/listing/{$listing->id}")->assertStatus(503);
        $this->assertDatabaseHas('waste_listings', ['id' => $listing->id]);
    }

    public function test_delete_does_not_remove_unmanaged_photo_path(): void
    {
        $this->createWasteRecordFixture();
        Storage::fake('public');
        Storage::disk('public')->put('shared/report.png', 'keep this file');
        $listing = WasteListing::create(array_merge($this->attributes(), [
            'photo_path' => 'shared/report.png',
        ]));

        $this->actingAs($this->commercialUser())
            ->delete("/bursa/listing/{$listing->id}")->assertRedirect('/bursa/listing');
        Storage::disk('public')->assertExists('shared/report.png');
    }

    public function test_record_created_through_production_crud_becomes_a_listing_source_and_is_then_protected(): void
    {
        $producer = User::factory()->create();
        $producer->assignRole('produksi');
        $this->actingAs($producer)->post('/produksi/limbah', [
            'waste_type' => 'Sisa rotan integrasi', 'weight_kg' => '5.125', 'condition' => 'Bagus',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $record = WasteRecord::firstOrFail();
        $before = $record->getRawOriginal();
        $this->actingAs($this->commercialUser())->get('/bursa/listing/create')->assertOk()
            ->assertViewHas('wasteRecordIds', fn ($ids) => $ids->contains($record->id));
        $this->post('/bursa/listing', array_replace($this->attributes(), [
            'waste_record_id' => $record->id, 'available_weight' => '5.125',
        ]))->assertRedirect('/bursa/listing')->assertSessionHasNoErrors();
        $listing = WasteListing::firstOrFail();
        $this->assertDatabaseHas('waste_listings', ['id' => $listing->id, 'waste_record_id' => $record->id, 'status' => 'draft']);
        $this->actingAs($producer)->get("/produksi/limbah/{$record->id}")->assertOk();
        $this->get("/produksi/limbah/{$record->id}/edit")->assertStatus(409);
        $this->put("/produksi/limbah/{$record->id}", [
            'waste_type' => 'Berubah', 'weight_kg' => 1, 'condition' => 'Bagus', 'status' => 'sold',
        ])->assertStatus(409);
        $this->delete("/produksi/limbah/{$record->id}")->assertStatus(409);
        $this->assertSame($before, $record->fresh()->getRawOriginal());
        $this->assertDatabaseHas('waste_listings', ['id' => $listing->id, 'waste_record_id' => $record->id]);
    }

    public function test_sold_and_unknown_sources_are_excluded_and_cannot_be_used_to_create_or_update_listings(): void
    {
        $this->createWasteRecordFixture();
        $available = WasteRecord::firstOrFail();
        $sold = WasteRecord::create(['waste_type' => 'Terjual', 'weight_kg' => 5, 'condition' => 'Bagus', 'status' => 'sold']);
        $unknown = WasteRecord::create(['waste_type' => 'Status lama', 'weight_kg' => 5, 'condition' => 'Bagus', 'status' => 'unknown']);
        $listing = WasteListing::create($this->attributes())->fresh();
        $before = $listing->getRawOriginal();
        $this->actingAs($this->commercialUser())->get('/bursa/listing/create')->assertOk()
            ->assertViewHas('wasteRecordIds', fn ($ids) => $ids->contains($available->id) && ! $ids->contains($sold->id) && ! $ids->contains($unknown->id));
        foreach ([$sold, $unknown] as $source) {
            $payload = array_replace($this->attributes(), ['waste_record_id' => $source->id]);
            $this->post('/bursa/listing', $payload)->assertSessionHasErrors('waste_record_id');
            $this->put("/bursa/listing/{$listing->id}", $payload)->assertSessionHasErrors('waste_record_id');
        }
        $this->assertSame($before, $listing->fresh()->getRawOriginal());
        $this->assertDatabaseCount('waste_listings', 1);
    }

    public function test_listing_weight_cannot_exceed_its_source_and_rejected_photo_changes_leave_no_orphans(): void
    {
        $this->createWasteRecordFixture();
        Storage::fake('public');
        $this->actingAs($this->commercialUser())->post('/bursa/listing', array_replace($this->attributes(), [
            'photo' => UploadedFile::fake()->image('original.jpg'),
        ]))->assertRedirect('/bursa/listing');
        $listing = WasteListing::firstOrFail();
        $before = $listing->getRawOriginal();
        $filesBefore = Storage::disk('public')->allFiles('waste-listings');
        $this->post('/bursa/listing', array_replace($this->attributes(), [
            'available_weight' => '5.001', 'photo' => UploadedFile::fake()->image('rejected-create.jpg'),
        ]))->assertSessionHasErrors('available_weight');
        $this->put("/bursa/listing/{$listing->id}", array_replace($this->attributes(), [
            'available_weight' => '5.001', 'photo' => UploadedFile::fake()->image('rejected-update.jpg'),
        ]))->assertSessionHasErrors('available_weight');
        $this->assertSame($before, $listing->fresh()->getRawOriginal());
        $this->assertSame($filesBefore, Storage::disk('public')->allFiles('waste-listings'));
        Storage::disk('public')->assertExists($listing->photo_path);
        $this->assertDatabaseCount('waste_listings', 1);
    }

    public function test_legacy_listed_source_can_be_kept_by_its_existing_listing_but_cannot_be_selected_by_another(): void
    {
        $this->createWasteRecordFixture();
        $current = WasteRecord::firstOrFail();
        $current->update(['status' => 'listed']);
        $other = WasteRecord::create(['waste_type' => 'Limbah listed lain', 'weight_kg' => 5, 'condition' => 'Bagus', 'status' => 'listed']);
        $listing = WasteListing::create($this->attributes());
        $this->actingAs($this->commercialUser())->get("/bursa/listing/{$listing->id}/edit")->assertOk()
            ->assertViewHas('wasteRecordIds', fn ($ids) => $ids->contains($current->id) && ! $ids->contains($other->id));
        $this->put("/bursa/listing/{$listing->id}", array_replace($this->attributes(), ['category' => 'Kategori revisi']))
            ->assertRedirect('/bursa/listing')->assertSessionHasNoErrors();
        $listing->refresh();
        $before = $listing->getRawOriginal();
        $this->put("/bursa/listing/{$listing->id}", array_replace($this->attributes(), ['waste_record_id' => $other->id]))
            ->assertSessionHasErrors('waste_record_id');
        $this->post('/bursa/listing', $this->attributes())->assertSessionHasErrors('waste_record_id');
        $this->assertSame($before, $listing->fresh()->getRawOriginal());
        $this->assertDatabaseHas('waste_records', ['id' => $current->id, 'status' => 'listed']);
        $this->assertDatabaseCount('waste_listings', 1);
    }

    public function test_deleted_sources_cannot_be_used_even_when_a_legacy_listing_still_references_them(): void
    {
        $this->createWasteRecordFixture();
        $source = WasteRecord::firstOrFail();
        $listing = WasteListing::create($this->attributes())->fresh();
        $before = $listing->getRawOriginal();
        // Historical data can contain an orphan because the marketplace table has no source foreign key.
        $source->delete();
        $this->actingAs($this->commercialUser())->post('/bursa/listing', $this->attributes())
            ->assertSessionHasErrors('waste_record_id');
        $this->put("/bursa/listing/{$listing->id}", $this->attributes())->assertSessionHasErrors('waste_record_id');
        $this->assertSame($before, $listing->fresh()->getRawOriginal());
        $this->assertDatabaseCount('waste_listings', 1);
        $this->assertDatabaseCount('waste_records', 0);
    }

    private function commercialUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('penjualan');

        return $user;
    }

    private function createWasteRecordFixture(): void
    {
        WasteRecord::create([
            'waste_type' => 'Sisa kayu uji',
            'weight_kg' => 5,
            'condition' => 'Bagus',
            'status' => 'available',
        ]);
    }

    private function attributes(): array
    {
        return [
            'waste_record_id' => 1,
            'category' => 'Kayu',
            'price_per_kg' => 1000,
            'available_weight' => 5,
        ];
    }
}
