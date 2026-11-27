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

    public function test_production_staff_can_publish_then_withdraw_a_passport(): void
    {
        $workOrder = $this->workOrder('completed');
        $passport = app(EcoPassportService::class)->createDraft($workOrder);
        $publish = route('passport.publish', $passport);
        $withdraw = route('passport.withdraw', $passport);
        $production = User::factory()->create()->assignRole('produksi');

        $this->actingAs($production)->get(route('passport.index'))->assertOk()
            ->assertSee($publish)->assertDontSee($withdraw);
        $this->post($publish, ['publication_status' => 'withdrawn'])->assertRedirect(route('passport.index'));
        $this->assertDatabaseHas('eco_passports', ['id' => $passport->id, 'publication_status' => 'published']);
        $this->get(route('passport.index'))->assertOk()
            ->assertSee('>Terbit</td>', false)->assertSee($withdraw)->assertDontSee($publish);

        $this->post($withdraw)->assertRedirect(route('passport.index'));
        $this->assertDatabaseHas('eco_passports', [
            'id' => $passport->id,
            'work_order_id' => $workOrder->id,
            'unique_code' => $passport->unique_code,
            'publication_status' => 'withdrawn',
        ]);
        $this->get(route('passport.index'))->assertOk()
            ->assertSee('>Ditarik</td>', false)->assertDontSee($publish)->assertDontSee($withdraw);
    }

    public function test_out_of_order_and_repeated_transitions_are_rejected(): void
    {
        $passport = app(EcoPassportService::class)->createDraft($this->workOrder('completed'));
        $publish = route('passport.publish', $passport);
        $withdraw = route('passport.withdraw', $passport);
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)->post($withdraw)->assertConflict();
        $this->assertDatabaseHas('eco_passports', ['id' => $passport->id, 'publication_status' => 'draft']);
        $this->post($publish)->assertRedirect(route('passport.index'));
        $this->post($publish)->assertConflict();
        $this->assertDatabaseHas('eco_passports', ['id' => $passport->id, 'publication_status' => 'published']);
        $this->post($withdraw)->assertRedirect(route('passport.index'));
        $this->post($withdraw)->assertConflict();
        $this->post($publish)->assertConflict();
        $this->assertDatabaseHas('eco_passports', ['id' => $passport->id, 'publication_status' => 'withdrawn']);
        $this->post(route('passport.publish', $passport->id + 1))->assertNotFound();
    }

    public function test_both_transitions_require_view_and_publish_permissions(): void
    {
        $workOrder = $this->workOrder('completed');
        $service = app(EcoPassportService::class);
        $draft = $service->createDraft($workOrder);
        $published = $service->createDraft($workOrder);
        $service->publish($published);
        $publish = route('passport.publish', $draft);
        $withdraw = route('passport.withdraw', $published);

        $this->post($publish)->assertRedirect(route('login'));
        $this->post($withdraw)->assertRedirect(route('login'));

        $viewer = User::factory()->create()->givePermissionTo('passport.view', 'passport.create');
        $this->actingAs($viewer)->get(route('passport.index'))->assertOk()
            ->assertDontSee($publish)->assertDontSee($withdraw);
        $this->post($publish)->assertForbidden();
        $this->post($withdraw)->assertForbidden();

        $publisher = User::factory()->create()->givePermissionTo('passport.publish');
        $this->actingAs($publisher)->post($publish)->assertForbidden();
        $this->post($withdraw)->assertForbidden();
        $this->assertDatabaseHas('eco_passports', ['id' => $draft->id, 'publication_status' => 'draft']);
        $this->assertDatabaseHas('eco_passports', ['id' => $published->id, 'publication_status' => 'published']);
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
