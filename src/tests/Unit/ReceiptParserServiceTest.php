<?php

namespace Tests\Unit;

use App\Services\ReceiptParserService;
use PHPUnit\Framework\TestCase;

class ReceiptParserServiceTest extends TestCase
{
    public function test_empty_fallback_returns_expected_structure()
    {
        $service = new ReceiptParserService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('emptyFallback');
        $method->setAccessible(true);

        $res = $method->invoke($service, 'Test error message');

        $this->assertFalse($res['success']);
        $this->assertEquals('Test error message', $res['error']);
        $this->assertIsArray($res['items']);
    }

    public function test_deduplicate_preserves_distinct_variants_with_same_base_name_and_price()
    {
        $service = new ReceiptParserService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('deduplicateItems');
        $method->setAccessible(true);

        $rawItems = [
            ['name' => 'Kopi Susu (Less Ice)', 'qty' => 1, 'price' => 18000],
            ['name' => 'Kopi Susu (Normal Ice)', 'qty' => 1, 'price' => 18000],
            ['name' => 'Ayam Geprek Level 1', 'qty' => 1, 'price' => 25000],
            ['name' => 'Ayam Geprek Level 3', 'qty' => 1, 'price' => 25000],
        ];

        $deduplicated = $method->invoke($service, $rawItems);

        $this->assertCount(4, $deduplicated);
        $this->assertEquals('Kopi Susu (Less Ice)', $deduplicated[0]['name']);
        $this->assertEquals('Kopi Susu (Normal Ice)', $deduplicated[1]['name']);
        $this->assertEquals('Ayam Geprek Level 1', $deduplicated[2]['name']);
        $this->assertEquals('Ayam Geprek Level 3', $deduplicated[3]['name']);
    }

    public function test_deduplicate_merges_identical_items_from_overlapping_screenshots()
    {
        $service = new ReceiptParserService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('deduplicateItems');
        $method->setAccessible(true);

        $rawItems = [
            ['name' => 'Kopi Susu (Less Ice)', 'qty' => 1, 'price' => 18000],
            ['name' => '  kopi susu (less ice)  ', 'qty' => 2, 'price' => 18000],
            ['name' => 'Roti Bakar Cokelat', 'qty' => 1, 'price' => 15000],
        ];

        $deduplicated = $method->invoke($service, $rawItems);

        $this->assertCount(2, $deduplicated);
        $this->assertEquals('Kopi Susu (Less Ice)', $deduplicated[0]['name']);
        $this->assertEquals(2, $deduplicated[0]['qty']);
        $this->assertEquals(18000, $deduplicated[0]['price']);
        $this->assertEquals('Roti Bakar Cokelat', $deduplicated[1]['name']);
    }

    public function test_deduplicate_merges_items_with_different_note_formatting_and_punctuation()
    {
        $service = new ReceiptParserService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('deduplicateItems');
        $method->setAccessible(true);

        $rawItems = [
            // Promo cup vs Regular cup (different price -> preserved!)
            ['name' => 'Iced Kopi Susu Astronauts - Regular 12oz (Catatan: Normal Sugar, Less Ice)', 'qty' => 1, 'price' => 21900],
            // Regular cup in screenshot 1 with parentheses
            ['name' => 'Iced Kopi Susu Astronauts - Regular 12oz (Catatan: Normal Sugar, Less Ice)', 'qty' => 1, 'price' => 23000],
            // Regular cup in screenshot 2 without parentheses
            ['name' => 'Iced Kopi Susu Astronauts - Regular 12oz Catatan: Normal Sugar, Less Ice', 'qty' => 1, 'price' => 23000],
            // Snack with unit formatting difference
            ['name' => 'Momogi Stick Jagung Bakar Box 20 pcs 90gram', 'qty' => 1, 'price' => 10500],
            ['name' => 'Momogi Stick Jagung Bakar Box 20pcs 90g', 'qty' => 1, 'price' => 10500],
        ];

        $deduplicated = $method->invoke($service, $rawItems);

        $this->assertCount(3, $deduplicated);
        $this->assertEquals(21900, $deduplicated[0]['price']);
        $this->assertEquals(23000, $deduplicated[1]['price']);
        $this->assertEquals(10500, $deduplicated[2]['price']);
    }

    public function test_apply_price_format_rules_unit_price_mode_keeps_prices_as_is()
    {
        $service = new ReceiptParserService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('applyPriceFormatRules');
        $method->setAccessible(true);

        $items = [
            ['name' => 'Kapal Api 350g', 'qty' => 3, 'price' => 19000],
            ['name' => 'Kopi Susu', 'qty' => 1, 'price' => 23000],
        ];

        $res = $method->invoke($service, $items, 'unit_price', 0, 0, 0, 80000);

        $this->assertFalse($res['auto_corrected']);
        $this->assertEquals(19000, $res['items'][0]['price']);
        $this->assertEquals(23000, $res['items'][1]['price']);
    }

    public function test_apply_price_format_rules_total_price_mode_divides_by_qty()
    {
        $service = new ReceiptParserService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('applyPriceFormatRules');
        $method->setAccessible(true);

        $items = [
            ['name' => 'Kapal Api 350g', 'qty' => 3, 'price' => 57000], // total was 57000 for 3pcs
            ['name' => 'Kopi Susu', 'qty' => 1, 'price' => 23000],
        ];

        $res = $method->invoke($service, $items, 'total_price', 0, 0, 0, 80000);

        $this->assertTrue($res['auto_corrected']);
        $this->assertEquals(19000, $res['items'][0]['price']); // 57000 / 3 = 19000
        $this->assertEquals(23000, $res['items'][1]['price']);
    }
}

