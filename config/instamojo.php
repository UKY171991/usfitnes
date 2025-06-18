<?php
/**
 * Instamojo Payment Gateway Configuration
 * Test and Live environment settings
 */

return [
    'test' => [
        'api_key' => 'YOUR_TEST_API_KEY',
        'auth_token' => 'YOUR_TEST_AUTH_TOKEN',
        'salt' => 'YOUR_TEST_SALT',
        'base_url' => 'https://test.instamojo.com/api/1.1/',
        'redirect_url' => 'https://yourdomain.com/usfitnes/thankyou.php',
        'webhook_url' => 'https://yourdomain.com/usfitnes/webhook.php',
    ],
    'live' => [
        'api_key' => 'YOUR_LIVE_API_KEY',
        'auth_token' => 'YOUR_LIVE_AUTH_TOKEN',
        'salt' => 'YOUR_LIVE_SALT',
        'base_url' => 'https://www.instamojo.com/api/1.1/',
        'redirect_url' => 'https://yourdomain.com/usfitnes/thankyou.php',
        'webhook_url' => 'https://yourdomain.com/usfitnes/webhook.php',
    ],
    'mode' => 'test', // 'test' or 'live'
    'environment' => 'test' // Switch to 'live' for production
];
?>
