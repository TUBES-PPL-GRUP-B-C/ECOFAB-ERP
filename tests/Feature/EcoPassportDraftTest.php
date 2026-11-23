<?php

namespace Tests\Feature;

use App\Models\EcoPassport;
use App\Models\WorkOrder;
use App\Services\EcoPassportService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EcoPassportDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_work_order_can_have_distinct_drafts_without_copying_emission_data(): void
    {
        $workOrder = WorkOrder::create([
            'wo_number' => 'WO-PASSPORT-01',
            'product_name' => 'Produk Uji',
            'target_quantity' => '10.00',
            'actual_quantity' => '8.00',
            'actual_emission' => '12.3400',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $service = app(EcoPassportService::class);
        $first = $service->createDraft($workOrder);
        $second = $service->createDraft($workOrder);

        $this->assertNotSame($first->unique_code, $second->unique_code);
        $this->assertMatchesRegularExpression('/^ECO-PASS-[0-9A-F-]{36}$/', $first->unique_code);
        $this->assertSame('draft', $first->fresh()->publication_status);
        $this->assertSame($workOrder->id, $first->workOrder->id);
        $this->assertEqualsCanonicalizing(['id', 'work_order_id', 'unique_code', 'publication_status', 'created_at', 'updated_at'], array_keys($first->fresh()->getAttributes()));
        $this->assertDatabaseCount('eco_passports', 2);
    }

    public function test_non_completed_work_orders_cannot_create_passports_even_with_stale_model(): void
    {
        $workOrder = WorkOrder::create([
            'wo_number' => 'WO-PASSPORT-02',
            'product_name' => 'Produk Uji',
            'target_quantity' => '10.00',
            'status' => 'completed',
        ]);
        WorkOrder::whereKey($workOrder->id)->update(['status' => 'cancelled']);

        try {
            app(EcoPassportService::class)->createDraft($workOrder);
            $this->fail('A cancelled Work Order must be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }

        $this->assertDatabaseCount('eco_passports', 0);
    }

    public function test_database_rejects_duplicate_codes_and_orphaned_work_orders(): void
    {
        $workOrder = WorkOrder::create([
            'wo_number' => 'WO-PASSPORT-03',
            'product_name' => 'Produk Uji',
            'target_quantity' => '10.00',
            'status' => 'completed',
        ]);
        $passport = app(EcoPassportService::class)->createDraft($workOrder);

        try {
            EcoPassport::query()->insert([
                'work_order_id' => $workOrder->id,
                'unique_code' => $passport->unique_code,
                'publication_status' => 'draft',
            ]);
            $this->fail('A duplicate code must be rejected.');
        } catch (QueryException) {
            $this->assertDatabaseCount('eco_passports', 1);
        }

        try {
            $workOrder->delete();
            $this->fail('A Work Order with passports must be retained.');
        } catch (QueryException) {
            $this->assertDatabaseCount('eco_passports', 1);
        }
    }
}
