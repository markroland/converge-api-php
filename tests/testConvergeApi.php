<?php

/*
    Assumptions:
        - PHPUnit is installed globally so no include is necessary
        - phpunit.xml is used to define path to code and bootstrap tests
*/

class convergeApiTest extends PHPUnit_Framework_TestCase
{

    protected $ConvergeApi;

    protected function setUp()
    {

        // Create new ConvergeApi object
        $this->ConvergeApi = new \markroland\Converge\ConvergeApi(
            'YOUR_CONVERGE_MERCHANTID',
            'YOUR_CONVERGE_USERID',
            'YOUR_CONVERGE_PIN',
            false
        );

    }

    protected function tearDown()
    {
        unset($this->ConvergeApi);
    }

    public function testPurchase()
    {

        $response = $this->ConvergeApi->purchase(
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

        $data = array();
        $lines = explode("\n", $response);
        foreach($lines as $line){
            $kvp = explode('=', $line);
            $data[$kvp[0]] = $kvp[1];
        }

        $this->assertSame('4025', $data['errorCode'] );
    }

    public function testRecurring()
    {

        $response = $this->ConvergeApi->recurring(
            array(
                'ssl_amount' => '9.99',
                'ssl_card_number' => '5000300020003003',
                'ssl_cvv2cvc2' => '123',
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

        $data = array();
        $lines = explode("\n", $response);
        foreach($lines as $line){
            $kvp = explode('=', $line);
            $data[$kvp[0]] = $kvp[1];
        }

        $this->assertSame('4025', $data['errorCode'] );
    }
}
