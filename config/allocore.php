<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Allocore Hub Integration Settings
    |--------------------------------------------------------------------------
    |
    | The Allocore Hub is the central Control Tower. Spoke tools (AuditPro,
    | InvoiceMaker, EasySOP, ...) push their KPIs here using a per-company,
    | per-tool API key sent in the "X-Allocore-Api-Key" header. Keys are
    | issued from the hub's Tools page and stored in the tool_accesses table.
    |
    */

    // Public base URL of this hub, handed to spoke tools when connecting.
    'hub_url' => env('ALLOCORE_HUB_URL', env('APP_URL')),
];
