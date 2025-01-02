<?php

return [
    'name' => env('APP_NAME', 'Laravel'),
    'env' => env('APP_ENV', 'production'),
    'password_min_length' => env('PASSWORD_MIN_LENGTH', 6),
    'password_max_length' => env('PASSWORD_MAX_LENGTH', 32),
    'iam_api_url' => env('IAM_API_URL', 'https://iam.orcapay.tech:9998/api'),
    'two_factor_auth_code_length' => env('TWO_FACTOR_AUTH_CODE_LENGTH',6),
];
