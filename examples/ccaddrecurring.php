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

// Submit a recurring payment
$PaymentProcessor->ccaddrecurring(
    array(
        'ssl_amount' => $signup['data']['total'],
        'ssl_card_number' => $signup['data']['cc_number'],
        'ssl_cvv2cvc2' => $signup['data']['cc_security_code'],
        'ssl_exp_date' => $signup['data']['cc_exp_month'] . substr($signup['data']['cc_exp_year'], -2),
        'ssl_avs_address' => $signup['data']['cc_address1'] . isset($signup['data']['cc_address1']) ? $signup['data']['cc_address1'] : '',
        'ssl_avs_zip' => $signup['data']['cc_postal_code'],
        'ssl_city' => $signup['data']['city'],
        'ssl_state' => $signup['data']['state'],
        'ssl_country' => $signup['data']['country'],
        'ssl_email' => $signup['data']['email'],
        'ssl_phone' => $signup['data']['phone'],
        'ssl_first_name' => $signup['data']['cc_firstname'],
        'ssl_last_name' => $signup['data']['cc_lastname'],
        'ssl_cardholder_ip' => $signup['data']['ip_address'],
        'ssl_next_payment_date' => $profile_start_date,
        'ssl_billing_cycle' => 'MONTHLY'
    )
);
// Display Converge API response
print('ConvergeApi->ccaddrecurring Response:' . "\n\n");
print_r($response);
