<?php

// Require the class
require(__DIR__ . './../src/ConvergeApi.php'); // ... directly
// require(__DIR__ . './../../../autoload.php'); // ... via Composer

// Create new PaymentProcessor object
$PaymentProcessor = new \markroland\Converge\ConvergeApi(
    'YOUR_CONVERGE_MERCHANTID',
    'YOUR_CONVERGE_USERID',
    'YOUR_CONVERGE_PIN',
    false
);

// Submit a purchase
$response = $PaymentProcessor->ccsale(
    array(
        'ssl_amount' => '9.99',
        'ssl_card_number' => '5000300020003003',
        'ssl_cvv2cvc2' => '123',
        'ssl_exp_date' => '1222',
        'ssl_avs_zip' => '37013',
        'ssl_avs_address' => '123 main',
        'ssl_last_name' => 'Smith'
    )
);

// Display Converge API response
print('ConvergeApi->ccsale Response:' . "\n\n");
print_r($response);