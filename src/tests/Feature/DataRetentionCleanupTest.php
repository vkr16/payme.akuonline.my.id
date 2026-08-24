<?php

namespace Tests\Feature;

use App\Models\Bill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataRetentionCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_retention_deletes_expired_paid_and_unpaid_bills(): void
    {
        Storage::fake('public');

        config([
            'payme.retention.paid_days' => 3,
            'payme.retention.unpaid_days' => 7,
        ]);

        // 1. Paid bill older than 3 days (5 days old) -> SHOULD BE DELETED
        $paidExpiredBill = Bill::create([
            'title' => 'Paid Expired Bill',
            'host_name' => 'Host 1',
            'slug' => 'paid-expired',
            'receipt_image_path' => 'bills/receipts/test1.png',
        ]);
        $item1 = $paidExpiredBill->items()->create(['name' => 'Menu 1', 'qty' => 1, 'price' => 10000]);
        $claim1 = $paidExpiredBill->claims()->create([
            'payer_name' => 'Budi',
            'amount' => 10000,
        ]);
        $claim1->claimItems()->create(['bill_item_id' => $item1->id, 'qty' => 1]);
        Storage::disk('public')->put('bills/receipts/test1.png', 'dummy content');

        DB::table('bills')->where('id', $paidExpiredBill->id)->update([
            'created_at' => now()->subDays(5)->toDateTimeString(),
            'updated_at' => now()->subDays(4)->toDateTimeString(),
        ]);
        DB::table('bill_claims')->where('id', $claim1->id)->update([
            'created_at' => now()->subDays(4)->toDateTimeString(),
            'updated_at' => now()->subDays(4)->toDateTimeString(),
        ]);

        // 2. Paid bill recent (1 day old) -> SHOULD BE KEPT
        $paidRecentBill = Bill::create([
            'title' => 'Paid Recent Bill',
            'host_name' => 'Host 2',
            'slug' => 'paid-recent',
        ]);
        $item2 = $paidRecentBill->items()->create(['name' => 'Menu 2', 'qty' => 1, 'price' => 20000]);
        $claim2 = $paidRecentBill->claims()->create([
            'payer_name' => 'Siti',
            'amount' => 20000,
        ]);
        $claim2->claimItems()->create(['bill_item_id' => $item2->id, 'qty' => 1]);

        DB::table('bills')->where('id', $paidRecentBill->id)->update([
            'created_at' => now()->subDays(1)->toDateTimeString(),
            'updated_at' => now()->subDays(1)->toDateTimeString(),
        ]);
        DB::table('bill_claims')->where('id', $claim2->id)->update([
            'created_at' => now()->subDays(1)->toDateTimeString(),
            'updated_at' => now()->subDays(1)->toDateTimeString(),
        ]);

        // 3. Unpaid bill older than 7 days (8 days old) -> SHOULD BE DELETED
        $unpaidExpiredBill = Bill::create([
            'title' => 'Unpaid Expired Bill',
            'host_name' => 'Host 3',
            'slug' => 'unpaid-expired',
        ]);
        $unpaidExpiredBill->items()->create(['name' => 'Menu 3', 'qty' => 1, 'price' => 50000]);
        DB::table('bills')->where('id', $unpaidExpiredBill->id)->update([
            'created_at' => now()->subDays(8)->toDateTimeString(),
            'updated_at' => now()->subDays(8)->toDateTimeString(),
        ]);

        // 4. Unpaid bill recent (4 days old) -> SHOULD BE KEPT
        $unpaidRecentBill = Bill::create([
            'title' => 'Unpaid Recent Bill',
            'host_name' => 'Host 4',
            'slug' => 'unpaid-recent',
        ]);
        $unpaidRecentBill->items()->create(['name' => 'Menu 4', 'qty' => 1, 'price' => 30000]);
        DB::table('bills')->where('id', $unpaidRecentBill->id)->update([
            'created_at' => now()->subDays(4)->toDateTimeString(),
            'updated_at' => now()->subDays(4)->toDateTimeString(),
        ]);

        // Hit the cleanup endpoint
        $response = $this->getJson('/clean-retention');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'deleted_summary' => [
                'paid_bills_deleted' => 1,
                'unpaid_bills_deleted' => 1,
                'total_bills_deleted' => 2,
                'claims_deleted' => 1,
                'items_deleted' => 2,
                'receipt_files_deleted' => 1,
            ]
        ]);

        // Verify database records
        $this->assertDatabaseMissing('bills', ['id' => $paidExpiredBill->id]);
        $this->assertDatabaseMissing('bills', ['id' => $unpaidExpiredBill->id]);

        $this->assertDatabaseHas('bills', ['id' => $paidRecentBill->id]);
        $this->assertDatabaseHas('bills', ['id' => $unpaidRecentBill->id]);

        // Verify file deletion
        Storage::disk('public')->assertMissing('bills/receipts/test1.png');
    }
}
