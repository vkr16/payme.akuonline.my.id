<?php

namespace App\Services;

class QrisService
{
    /**
     * Extract Merchant Info from raw static EMVCo QRIS string.
     */
    public function extractMerchantInfo(string $qrisCode): array
    {
        $merchantName = 'Tidak diketahui';
        $merchantLocation = 'Tidak diketahui';

        // Tag 59: Merchant Name
        if (preg_match('/59(\d{2})([^\d]{2,})/', $qrisCode, $nameMatch)) {
            $len = (int) $nameMatch[1];
            $merchantName = substr($nameMatch[2], 0, $len);
        }

        // Tag 60: Merchant Location / City
        if (preg_match('/60(\d{2})([^\d]{2,})/', $qrisCode, $locMatch)) {
            $len = (int) $locMatch[1];
            $merchantLocation = substr($locMatch[2], 0, $len);
        }

        return [
            'merchant_name' => trim($merchantName),
            'merchant_city' => trim($merchantLocation),
        ];
    }

    /**
     * Convert Static QRIS code to Dynamic QRIS code with specified nominal amount.
     */
    public function convertToDynamic(string $qrisCode, float|int|string $amount): string
    {
        // Format integer nominal (no decimals for IDR)
        $nominalStr = (string) (int) round((float) $amount);

        // Remove trailing 4-char CRC if present
        $cleanQris = substr($qrisCode, 0, -4);
        // Replace Tag 010211 (Static) with 010212 (Dynamic)
        $cleanQris = str_replace('010211', '010212', $cleanQris);

        // Split by 5802ID (Country Code tag)
        $parts = explode('5802ID', $cleanQris, 2);
        if (count($parts) < 2) {
            return $qrisCode; // Fallback if format is non-standard
        }

        $prefix = $parts[0];
        $suffix = $parts[1];

        // Tag 54: Transaction Amount
        $lenStr = str_pad((string) strlen($nominalStr), 2, '0', STR_PAD_LEFT);
        $nominalData = '54' . $lenStr . $nominalStr;

        $payloadToCrc = $prefix . $nominalData . '5802ID' . $suffix;
        $crc = $this->calculateCrc16($payloadToCrc);

        return $payloadToCrc . $crc;
    }

    /**
     * CRC16-CCITT (0xFFFF init, 0x1021 poly) calculation for EMVCo QRIS.
     */
    public function calculateCrc16(string $str): string
    {
        $crc = 0xFFFF;
        $len = strlen($str);

        for ($c = 0; $c < $len; $c++) {
            $crc ^= (ord($str[$c]) << 8);
            for ($i = 0; $i < 8; $i++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc & 0xFFFF), 4, '0', STR_PAD_LEFT));
    }
}
