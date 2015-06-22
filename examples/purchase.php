<?php

// Require the Composer autoloader
require(__DIR__ . './../../../autoload.php');

// Create new PaymentProcessor object
$PaymentProcessor = new \markroland\Converge\ConvergeApi(
    'YOUR_CONVERGE_MERCHANTID',
    'YOUR_CONVERGE_USERID',
    'YOUR_CONVERGE_PIN',
    false
);

// Submit a purchase
$response = $PaymentProcessor->purchase(
    array(
        'ssl_amount' => '9.99',
        'ssl_card_number' => '5000300020003003',
        'ssl_exp_date' => '1222',
        'ssl_avs_zip' => '37013',
        'ssl_avs_address' => '123 main',
        'ssl_last_name' => 'Smith'
    )
);

// Display Converge API response
print('ConvergeApi->purchase Response:' . "\n\n");
var_dump($response);
print("\n");

// HTTP Debugging info
print('HTTP Debugging:' . "\n\n");
var_dump($PaymentProcessor->debug);
print("\n");
