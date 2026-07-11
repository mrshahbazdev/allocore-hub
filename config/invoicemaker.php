<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Invoice Maker Integration
    |--------------------------------------------------------------------------
    |
    | When a PayPal payment is captured, Allocore sends the order details to
    | the Invoice Maker platform so an invoice is automatically generated.
    |
    */

    'api_key' => env('INVOICE_MAKER_API_KEY'),

    'base_url' => env('INVOICE_MAKER_URL', 'https://invoice.allocore.de'),

];
