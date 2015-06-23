<?php

/*
    Assumptions:
        - PHPUnit is installed globally so no include is necessary
        - phpunit.xml.dist is used to define path to code and bootstrap tests
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

    public function test_ccsale()
    {

        $response = $this->ConvergeApi->ccsale(
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

        $this->assertSame('4025', $response['errorCode'] );
    }

    public function test_ccsale_live()
    {

        unset($this->ConvergeApi);

        // Create new ConvergeApi object
        $this->ConvergeApi = new \markroland\Converge\ConvergeApi(
            'YOUR_CONVERGE_MERCHANTID',
            'YOUR_CONVERGE_USERID',
            'YOUR_CONVERGE_PIN',
            true
        );

        $response = $this->ConvergeApi->ccsale(
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

        $this->assertSame('4025', $response['errorCode'] );
    }

    public function test_ccaddinstall()
    {

        $response = $this->ConvergeApi->ccaddinstall(
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

        $this->assertSame('4025', $response['errorCode'] );
    }
}
