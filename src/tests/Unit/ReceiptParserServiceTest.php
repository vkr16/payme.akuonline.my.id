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
}
