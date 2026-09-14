<?php

namespace Tests\Feature;

use App\Models\Bill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillPaymentDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_bill_page_displays_overall_delivery_fee_and_discount(): void
    {
        $bill = Bill::create([
            'title' => 'Makan Bersama Kantor',
            'host_name' => 'Fikri Host',
            'slug' => 'detailtest1',
            'delivery_fee' => 15000,
            'service_fee' => 3000,
            'discount' => 10000,
        ]);

        $item1 = $bill->items()->create([
            'name' => 'Nasi Padang Komplit',
            'qty' => 2,
            'price' => 25000,
        ]);

        $item2 = $bill->items()->create([
            'name' => 'Es Teh Manis',
            'qty' => 2,
            'price' => 5000,
        ]);

        // Subtotal = (2*25000) + (2*5000) = 60000
        // Net extra = 15000 + 3000 - 10000 = 8000
        // Total = 68000

        $response = $this->get('/b/' . $bill->slug);

        $response->assertStatus(200);
        $response->assertSee('Rincian Biaya Keseluruhan Tagihan');
        $response->assertSee('Total Ongkos Kirim:');
        $response->assertSee('15.000');
        $response->assertSee('Total Diskon / Promo:');
        $response->assertSee('10.000');
        $response->assertSee('Total Biaya Layanan:');
        $response->assertSee('3.000');
        $response->assertSee('Proporsi Biaya & Diskon Kamu:', false);
        $response->assertSee('Proporsi Ongkir:');
        $response->assertSee('Proporsi Diskon:');
    }

    public function test_dynamic_qris_returns_proportional_delivery_and_discount_breakdown(): void
    {
        $bill = Bill::create([
            'title' => 'Makan Siang',
            'host_name' => 'Host Budi',
            'slug' => 'detailtest2',
            'qris_static_payload' => '00020101021126580016ID.CO.QRIS.WWW011893600914000005115102155910LEO SUWANDI6007JAKARTA5802ID63041234',
            'delivery_fee' => 20000,
            'service_fee' => 4000,
            'discount' => 10000,
        ]);

        // Total items subtotal = 100000
        $item1 = $bill->items()->create([
            'name' => 'Pizza Super Supreme',
            'qty' => 1,
            'price' => 50000,
        ]);

        $item2 = $bill->items()->create([
            'name' => 'Pasta Carbonara',
            'qty' => 1,
            'price' => 50000,
        ]);

        // Participant chooses Item 1 (50000 out of 100000 = 50% proportion)
        // delivery_fee_share = 50% * 20000 = 10000
        // service_fee_share = 50% * 4000 = 2000
        // discount_share = 50% * 10000 = 5000
        // fee_share = 10000 + 2000 - 5000 = 7000
        // exact_payable = 50000 + 7000 = 57000

        $res = $this->postJson('/b/' . $bill->slug . '/qris', [
            'items' => [$item1->id => 1],
            'round_up' => false,
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'items_subtotal' => 50000,
            'total_delivery_fee' => 20000,
            'total_service_fee' => 4000,
            'total_discount' => 10000,
            'delivery_fee_share' => 10000,
            'service_fee_share' => 2000,
            'discount_share' => 5000,
            'fee_share' => 7000,
            'exact_payable' => 57000,
            'total_payable' => 57000,
        ]);
    }

    public function test_claim_payment_calculates_with_proportional_discount_and_delivery(): void
    {
        $bill = Bill::create([
            'title' => 'Kopi Bersama',
            'host_name' => 'Host Budi',
            'slug' => 'detailtest3',
            'delivery_fee' => 10000,
            'discount' => 6000,
        ]);

        $item = $bill->items()->create([
            'name' => 'Kopi Susu Gula Aren',
            'qty' => 2,
            'price' => 20000, // total subtotal = 40000
        ]);

        // 1 cup = 20000 out of 40000 (50% proportion)
        // delivery share = 50% * 10000 = 5000
        // discount share = 50% * 6000 = 3000
        // fee share = 5000 - 3000 = 2000
        // exact paid = 20000 + 2000 = 22000

        $res = $this->postJson('/b/' . $bill->slug . '/claim', [
            'payer_name' => 'Candra',
            'items' => [$item->id => 1],
            'round_up' => false,
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'amount' => 22000,
        ]);

        $this->assertDatabaseHas('bill_claims', [
            'bill_id' => $bill->id,
            'payer_name' => 'Candra',
            'amount' => 22000,
        ]);
    }
}
