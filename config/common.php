<?php

return [
    'name' => env('APP_NAME', 'Laravel'),
    'env' => env('APP_ENV', 'production'),
    'password_min_length' => env('PASSWORD_MIN_LENGTH', 6),
    'password_max_length' => env('PASSWORD_MAX_LENGTH', 32),
    'iam_api_url' => env('IAM_API_URL', 'https://iam.orcapay.tech:9998/api'),
    'two_factor_auth_code_length' => env('TWO_FACTOR_AUTH_CODE_LENGTH',6),
    'sender_address' => env('EMAIL_SENDER_ADDRESS','donotreply@scancheck.io'),
    'sender_name' => env('EMAIL_SENDER_NAME','Scancheck'),
    '2fa_body'       => env('EMAIL_2FA_BODY','Here is your requested 2FA code: %s'),
    '2fa_subject'    => env('EMAIL_2FA_SUBJECT','Your requested 2FA code'),
];
