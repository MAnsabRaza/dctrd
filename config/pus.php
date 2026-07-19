    <?php

    return [
        // PUS (Premium URL Shortener) subdomain base URL, e.g. https://s.rocket-lms.com
        'base_url' => env('PUS_BASE_URL', 'https://s.dctrd.us'),

        // PUS API token (Settings > API mein subdomain setup ke time milega)
        'api_token' => env('PUS_API_TOKEN'),

        // QR image ka default size (pixels)
        'qr_size' => env('PUS_QR_SIZE', 400),

        // App ke andar redirect route prefix (e.g. domain.com/r/{code})
        'redirect_prefix' => 'r',
    ];
