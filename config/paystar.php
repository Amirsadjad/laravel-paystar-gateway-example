<?php

return [
    'gateway_id' => env('PAYSTAR_GATEWAY_ID'),
    'encryption_key' => env('PAYSTAR_ENCRYPTION_KEY'),
    'url' => env('PAYSTAR_URL', 'https://core.paystar.ir/api/pardakht')
];
