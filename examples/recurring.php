<?php

// Include Ontraport class
include('Converge.class.php');

// Include credentials.
// Defines CONVERGE_MERCHANTID, CONVERGE_USERID & CONVERGE_PIN
include('credentials.php');

// Create new PaymentProcessor object
$PaymentProcessor = new \markroland\PaymentProcessor\Converge\Converge(
    CONVERGE_MERCHANTID,
    CONVERGE_USERID,
    CONVERGE_PIN,
    false
);

// Add contact
$response = $PaymentProcessor->recurring(
    array(
        'ssl_amount' => '9.99',
        'ssl_card_number' => '5000300020003003',
        'ssl_exp_date' => '1222',
        'ssl_avs_zip' => '37013',
        'ssl_avs_address' => '123 main',
        'ssl_last_name' => 'Smith',
        'ssl_total_installments' => '12',
        'ssl_next_payment_date' => '10/01/2014',
        'ssl_start_payment_date' => '10/01/2014',
        'ssl_billing_cycle' => 'MONTHLY'
    )
);

// Display unformatted (XML) response
echo $response;

// Debugging info
// var_dump($PaymentProcessor->debug);
