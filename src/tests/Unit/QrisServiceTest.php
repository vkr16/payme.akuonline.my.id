<?php

namespace Tests\Unit;

use App\Services\QrisService;
use PHPUnit\Framework\TestCase;

class QrisServiceTest extends TestCase
{
    public function test_qris_merchant_extraction()
    {
        $service = new QrisService();
        $sampleQris = "00020101021126580016ID.CO.QRIS.WWW011893600914000005115102155915LEO STORE CAFE6007JAKARTA5802ID6304A1B2";

        $info = $service->extractMerchantInfo($sampleQris);

        $this->assertEquals('LEO STORE CAFE', $info['merchant_name']);
        $this->assertEquals('JAKARTA', $info['merchant_city']);
    }

    public function test_qris_static_to_dynamic_conversion()
    {
        $service = new QrisService();
        $staticQris = "00020101021126580016ID.CO.QRIS.WWW011893600914000005115102155910LEO SUWANDI6007JAKARTA5802ID63041234";

        $dynamicQris = $service->convertToDynamic($staticQris, 49500);

        // Check dynamic indicator tag 010212
        $this->assertStringContainsString('010212', $dynamicQris);
        // Check amount tag 540549500
        $this->assertStringContainsString('540549500', $dynamicQris);
        // Check standard 4-char CRC16 at the end
        $this->assertEquals(4, strlen(substr($dynamicQris, -4)));
    }
}
