<?php

namespace Tests\Feature;

use App\Models\Bill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BillReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_returns_404_when_bill_has_no_receipt(): void
    {
        $bill = Bill::create([
            'title' => 'Test Patungan',
            'host_name' => 'Host Test',
            'slug' => 'testslug1',
            'receipt_image_path' => null,
        ]);

        $response = $this->get('/b/' . $bill->slug . '/receipt');
        $response->assertStatus(404);
    }

    public function test_receipt_returns_file_response_when_image_exists(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('struk_test.jpg');
        $storedPath = $file->store('bills/receipts', 'public');

        $bill = Bill::create([
            'title' => 'Test Patungan',
            'host_name' => 'Host Test',
            'slug' => 'testslug2',
            'receipt_image_path' => $storedPath,
        ]);

        $response = $this->get('/b/' . $bill->slug . '/receipt');
        $response->assertStatus(200);
    }
}
