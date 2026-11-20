<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteListing;
use App\Models\WasteOrder;
use App\Models\WasteRecord;
use App\Services\WasteMarketService;
use App\Services\WasteOrderService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class WasteMarketServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_publication_persists_a_real_listing_and_enforces_shared_source_capacity(): void
    {
        $record = WasteRecord::create(['waste_type' => 'Kayu uji', 'weight_kg' => 5, 'condition' => 'Bagus']);
        $service = app(WasteMarketService::class);
        $this->assertTrue($service->publishListing($record->id, 'Kayu', 3.125, 1200.50));
        $this->assertDatabaseHas('waste_listings', ['waste_record_id' => $record->id, 'status' => 'published', 'available_weight' => '3.125']);
        try {
            $service->publishListing($record->id, 'Kayu kedua', 2, 1200);
            $this->fail('Allocation exceeding the measured source must fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('available_weight', $exception->errors());
        }
        $this->assertDatabaseCount('waste_listings', 1);
        $this->assertSame('5.000', $record->fresh()->weight_kg);
    }

    public function test_legacy_service_decisions_delegate_to_atomic_order_processing(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $record = WasteRecord::create(['waste_type' => 'Kayu uji', 'weight_kg' => 5, 'condition' => 'Bagus']);
        $service = app(WasteMarketService::class);
        $service->publishListing($record->id, 'Kayu', 5, 1200);
        $listing = WasteListing::sole();
        $order = app(WasteOrderService::class)->create([
            'waste_listing_id' => $listing->id, 'buyer_name' => 'Pembeli uji',
            'buyer_contact' => 'buyer@example.test', 'requested_weight' => '2.125',
        ]);
        $this->assertTrue($service->approveOrder($order->id, $admin->id));
        $this->assertSame('2.875', $listing->fresh()->available_weight);
        $this->assertDatabaseHas('waste_orders', ['id' => $order->id, 'status' => 'approved', 'approved_by' => $admin->id]);
        try {
            $service->approveOrder($order->id, $admin->id);
            $this->fail('Repeated decisions must fail.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
        $second = app(WasteOrderService::class)->create([
            'waste_listing_id' => $listing->id, 'buyer_name' => 'Pembeli kedua',
            'buyer_contact' => 'buyer2@example.test', 'requested_weight' => '1.000',
        ]);
        $this->assertTrue($service->rejectOrder($second->id, $admin->id, 'Ditolak untuk pengujian'));
        $this->assertSame('2.875', $listing->fresh()->available_weight);
        $this->assertSame('Ditolak untuk pengujian', $second->fresh()->decision_reason);
        $this->assertSame(2, WasteOrder::count());
    }
}
