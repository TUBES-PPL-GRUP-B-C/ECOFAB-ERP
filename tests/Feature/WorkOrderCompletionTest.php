<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteRecord;
use App\Models\WorkOrder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_finish_action_requires_authentication(): void
    {
        $workOrder = $this->inProgressWorkOrder();

        $this->post("/produksi/wo/{$workOrder->id}/selesai", [
            'actual_quantity' => '10.00',
        ])->assertRedirect('/login');

        $this->assertSame('in_progress', $workOrder->fresh()->status);
        $this->assertNull($workOrder->fresh()->completed_at);
        $this->assertDatabaseCount('waste_records', 0);
    }

    public function test_finish_action_requires_wo_finish_permission(): void
    {
        $workOrder = $this->inProgressWorkOrder();

        foreach (['hr', 'pembelian', 'gudang', 'penjualan'] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);
            $this->actingAs($user);

            $this->post("/produksi/wo/{$workOrder->id}/selesai", [
                'actual_quantity' => '10.00',
            ])->assertForbidden();

            $this->assertSame('in_progress', $workOrder->fresh()->status);
        }
    }

    public function test_production_staff_can_complete_work_order_with_automatic_waste_recording(): void
    {
        $user = $this->productionUser();
        $this->actingAs($user);

        $workOrder = $this->inProgressWorkOrder([
            'wo_number' => 'WO-FINISH-001',
            'product_name' => 'Meja Kayu FSC',
            'target_quantity' => 10,
        ]);

        $response = $this->post("/produksi/wo/{$workOrder->id}/selesai", [
            'actual_quantity' => '9.50',
            'actual_emission' => '12.4500',
            'waste_type' => 'Sisa Potongan Kayu FSC',
            'waste_weight_kg' => '2.750',
            'waste_condition' => 'Bagus',
        ]);

        $response->assertRedirect(route('produksi.wo.show', $workOrder))
            ->assertSessionHas('success');

        $fresh = $workOrder->fresh();
        $this->assertSame('completed', $fresh->status);
        $this->assertSame('9.50', $fresh->actual_quantity);
        $this->assertSame('12.4500', $fresh->actual_emission);
        $this->assertNotNull($fresh->completed_at);

        // Verify automatic waste record created (FR-18 -> FR-22)
        $this->assertDatabaseHas('waste_records', [
            'work_order_id' => $workOrder->id,
            'waste_type' => 'Sisa Potongan Kayu FSC',
            'weight_kg' => 2.750,
            'condition' => 'Bagus',
            'status' => 'available',
        ]);

        $waste = WasteRecord::where('work_order_id', $workOrder->id)->firstOrFail();
        $this->assertSame($workOrder->id, $waste->work_order_id);
        $this->assertTrue($waste->isAvailable());
    }

    public function test_administrator_can_also_complete_work_order(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $workOrder = $this->inProgressWorkOrder();

        $this->post("/produksi/wo/{$workOrder->id}/selesai", [
            'actual_quantity' => '10.00',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('completed', $workOrder->fresh()->status);
    }

    public function test_cannot_complete_draft_or_already_completed_or_cancelled_work_orders(): void
    {
        $user = $this->productionUser();
        $this->actingAs($user);

        $draft = WorkOrder::create([
            'wo_number' => 'WO-DRAFT-01',
            'product_name' => 'Kursi Rotan',
            'target_quantity' => 5,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $completed = WorkOrder::create([
            'wo_number' => 'WO-DONE-01',
            'product_name' => 'Kursi Rotan',
            'target_quantity' => 5,
            'actual_quantity' => 5,
            'status' => 'completed',
            'completed_at' => now(),
            'created_by' => $user->id,
        ]);

        $cancelled = WorkOrder::create([
            'wo_number' => 'WO-CANCEL-01',
            'product_name' => 'Kursi Rotan',
            'target_quantity' => 5,
            'status' => 'cancelled',
            'created_by' => $user->id,
        ]);

        foreach ([$draft, $completed, $cancelled] as $wo) {
            $this->post("/produksi/wo/{$wo->id}/selesai", [
                'actual_quantity' => '5.00',
            ])->assertStatus(409);

            $this->assertSame($wo->status, $wo->fresh()->status);
        }
    }

    public function test_completion_validation_rejects_invalid_inputs(): void
    {
        $user = $this->productionUser();
        $this->actingAs($user);
        $workOrder = $this->inProgressWorkOrder();

        // 1. Missing actual_quantity
        $this->post("/produksi/wo/{$workOrder->id}/selesai", [])
            ->assertSessionHasErrors('actual_quantity');

        // 2. Zero or negative actual_quantity
        $this->post("/produksi/wo/{$workOrder->id}/selesai", ['actual_quantity' => 0])
            ->assertSessionHasErrors('actual_quantity');

        $this->post("/produksi/wo/{$workOrder->id}/selesai", ['actual_quantity' => -5])
            ->assertSessionHasErrors('actual_quantity');

        // 3. Waste weight required when waste_type is specified
        $this->post("/produksi/wo/{$workOrder->id}/selesai", [
            'actual_quantity' => 10,
            'waste_type' => 'Serbuk Kayu',
        ])->assertSessionHasErrors('waste_weight_kg');

        // 4. Zero waste weight
        $this->post("/produksi/wo/{$workOrder->id}/selesai", [
            'actual_quantity' => 10,
            'waste_type' => 'Serbuk Kayu',
            'waste_weight_kg' => 0,
        ])->assertSessionHasErrors('waste_weight_kg');

        $this->assertSame('in_progress', $workOrder->fresh()->status);
        $this->assertDatabaseCount('waste_records', 0);
    }

    public function test_completion_without_waste_record_is_supported(): void
    {
        $user = $this->productionUser();
        $this->actingAs($user);
        $workOrder = $this->inProgressWorkOrder();

        $this->post("/produksi/wo/{$workOrder->id}/selesai", [
            'actual_quantity' => '12.00',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('completed', $workOrder->fresh()->status);
        $this->assertSame('12.00', $workOrder->fresh()->actual_quantity);
        $this->assertDatabaseCount('waste_records', 0);
    }

    private function inProgressWorkOrder(array $overrides = []): WorkOrder
    {
        $user = User::factory()->create();

        return WorkOrder::create(array_replace([
            'wo_number' => 'WO-PROG-'.uniqid(),
            'product_name' => 'Produk Uji',
            'target_quantity' => 10,
            'status' => 'in_progress',
            'start_date' => today(),
            'created_by' => $user->id,
        ], $overrides));
    }

    private function productionUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('produksi');

        return $user;
    }
}
