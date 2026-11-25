<?php

namespace Tests\Feature;

use App\Models\EcoPassport;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\EcoPassportService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcoPassportPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_and_sales_staff_cannot_list_or_create_passports(): void
    {
        $workOrder = $this->workOrder('completed');
        $payload = ['work_order_id' => $workOrder->id];

        $this->get(route('passport.index'))->assertRedirect(route('login'));
        $this->post(route('passport.store'), $payload)->assertRedirect(route('login'));

        $sales = User::factory()->create()->assignRole('penjualan');
        $this->actingAs($sales)->get(route('passport.index'))->assertForbidden();
        $this->post(route('passport.store'), $payload)->assertForbidden();
        $this->assertDatabaseCount('eco_passports', 0);
    }

    public function test_creating_a_passport_requires_both_existing_permissions(): void
    {
        $workOrder = $this->workOrder('completed');
        $passport = app(EcoPassportService::class)->createDraft($workOrder);
        $payload = ['work_order_id' => $workOrder->id];

        $viewer = User::factory()->create()->givePermissionTo('passport.view');
        $this->actingAs($viewer)->get(route('passport.index'))->assertOk()
            ->assertSee($passport->unique_code)
            ->assertDontSee('name="work_order_id"', false);
        $this->post(route('passport.store'), $payload)->assertForbidden();

        $creator = User::factory()->create()->givePermissionTo('passport.create');
        $this->actingAs($creator)->post(route('passport.store'), $payload)->assertForbidden();
        $this->assertDatabaseCount('eco_passports', 1);
    }

    public function test_production_staff_can_create_and_list_a_draft_from_a_completed_work_order(): void
    {
        $completed = $this->workOrder('completed');
        $unfinished = $this->workOrder('in_progress');
        $production = User::factory()->create()->assignRole('produksi');

        $this->actingAs($production)->get(route('passport.index'))->assertOk()
            ->assertSee($completed->wo_number)
            ->assertDontSee($unfinished->wo_number)
            ->assertSee('Belum ada Eco-Passport');

        $this->post(route('passport.store'), [
            'work_order_id' => $completed->id,
            'publication_status' => 'published',
            'unique_code' => 'CLIENT-CODE',
        ])->assertRedirect(route('passport.index'))
            ->assertSessionHas('success');

        $passport = EcoPassport::query()->sole();
        $this->assertSame($completed->id, $passport->work_order_id);
        $this->assertSame('draft', $passport->publication_status);
        $this->assertMatchesRegularExpression('/^ECO-PASS-[0-9A-F-]{36}$/', $passport->unique_code);
        $this->get(route('passport.index'))->assertOk()
            ->assertSee($passport->unique_code)
            ->assertSeeInOrder(['<td', $completed->wo_number, $completed->product_name, 'Draft'], false);
    }

    public function test_invalid_or_ineligible_work_orders_do_not_create_a_draft(): void
    {
        $unfinished = $this->workOrder('in_progress');
        $cancelled = $this->workOrder('cancelled');
        $production = User::factory()->create()->assignRole('produksi');
        $this->actingAs($production);

        foreach ([null, 'invalid', [$unfinished->id], 999999, $unfinished->id, $cancelled->id] as $id) {
            $this->from(route('passport.index'))->post(route('passport.store'), ['work_order_id' => $id])
                ->assertRedirect(route('passport.index'))
                ->assertSessionHasErrors('work_order_id');
        }

        $this->get(route('passport.index'))->assertOk()
            ->assertSee('Pilih Work Order yang sudah selesai.');
        $this->assertDatabaseCount('eco_passports', 0);
    }

    public function test_public_passport_route_still_serves_the_existing_scan_view(): void
    {
        $workOrder = $this->workOrder('completed');
        $passport = app(EcoPassportService::class)->createDraft($workOrder);

        $this->get(route('public.passport.show', ['kode' => $passport->unique_code]))
            ->assertOk()
            ->assertViewIs('public.passport_scan')
            ->assertDontSee($workOrder->product_name);
    }

    private function workOrder(string $status): WorkOrder
    {
        return WorkOrder::create([
            'wo_number' => 'WO-PASSPORT-'.strtoupper($status),
            'product_name' => 'Produk Uji Passport '.$status,
            'target_quantity' => '10.00',
            'status' => $status,
        ]);
    }
}
