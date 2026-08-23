<?php

namespace Tests\Feature;

use App\Models\Bill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillRoundingTest extends TestCase
{
    use RefreshDatabase;

    public function test_qris_generation_with_round_up(): void
    {
        $bill = Bill::create([
            'title' => 'Makan Siang',
            'host_name' => 'Host Budi',
            'slug' => 'roundslug1',
            'qris_static_payload' => '00020101021126580016ID.CO.QRIS.WWW011893600914000005115102155910LEO SUWANDI6007JAKARTA5802ID63041234',
            'delivery_fee' => 2500,
        ]);

        $item = $bill->items()->create([
            'name' => 'Nasi Goreng',
            'qty' => 1,
            'price' => 15000,
        ]);

        // Total exact = 15000 + 2500 = 17500
        // Without round_up: 17500
        $resNormal = $this->postJson('/b/' . $bill->slug . '/qris', [
            'items' => [$item->id => 1],
            'round_up' => false,
        ]);

        $resNormal->assertStatus(200);
        $resNormal->assertJson([
            'success' => true,
            'exact_payable' => 17500,
            'round_up_extra' => 0,
            'total_payable' => 17500,
        ]);

        // With round_up: ceil(17500 / 1000) * 1000 = 18000 (extra 500)
        $resRound = $this->postJson('/b/' . $bill->slug . '/qris', [
            'items' => [$item->id => 1],
            'round_up' => true,
        ]);

        $resRound->assertStatus(200);
        $resRound->assertJson([
            'success' => true,
            'exact_payable' => 17500,
            'round_up_extra' => 500,
            'total_payable' => 18000,
        ]);
    }

    public function test_claim_payment_with_round_up(): void
    {
        $bill = Bill::create([
            'title' => 'Makan Siang',
            'host_name' => 'Host Budi',
            'slug' => 'roundslug2',
            'delivery_fee' => 1350,
        ]);

        $item = $bill->items()->create([
            'name' => 'Ayam Bakar',
            'qty' => 1,
            'price' => 20000,
        ]);

        // Exact = 20000 + 1350 = 21350
        // Rounded = 22000
        $resClaim = $this->postJson('/b/' . $bill->slug . '/claim', [
            'payer_name' => 'Andi',
            'items' => [$item->id => 1],
            'round_up' => true,
        ]);

        $resClaim->assertStatus(200);
        $resClaim->assertJson([
            'success' => true,
            'amount' => 22000,
        ]);

        $this->assertDatabaseHas('bill_claims', [
            'bill_id' => $bill->id,
            'payer_name' => 'Andi',
            'amount' => 22000,
        ]);
    }
}
