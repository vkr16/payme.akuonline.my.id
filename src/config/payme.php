<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PayMe Data Retention Settings (in Calendar Days)
    |--------------------------------------------------------------------------
    |
    | Defines how many days data remains stored before automatic deletion:
    | - paid_days: Calendar days to keep fully paid bills.
    | - unpaid_days: Calendar days to keep active/unpaid bills.
    |
    */

    'retention' => [
        'paid_days' => (int) env('PAYME_RETENTION_PAID_DAYS', 3),
        'unpaid_days' => (int) env('PAYME_RETENTION_UNPAID_DAYS', 7),
    ],

];
