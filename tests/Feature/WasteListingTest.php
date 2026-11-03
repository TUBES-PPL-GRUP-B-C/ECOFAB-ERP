<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteListing;
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
        $this->post('/bursa/listing', [])->assertRedirect('/login');

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
        $this->actingAs($this->commercialUser())
            ->post('/bursa/listing', $this->attributes())
            ->assertStatus(503);
        $this->assertDatabaseCount('waste_listings', 0);
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

    private function commercialUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('penjualan');

        return $user;
    }

    private function createWasteRecordFixture(): void
    {
        Schema::create('waste_records', function (Blueprint $table) {
            $table->id();
        });
        \Illuminate\Support\Facades\DB::table('waste_records')->insert(['id' => 1]);
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
