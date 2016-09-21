<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Middleware;

class ConvergeApiTest extends PHPUnit_Framework_TestCase
{
    private $merchantId = 'YOUR_CONVERGE_MERCHANTID';
    private $userId = 'YOUR_CONVERGE_USERID';
    private $pin = 'YOUR_CONVERGE_PIN';

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    private function _setGuzzleMock($responseCode, $responseBody) {
        return $historyContainer;
    }

    public function _test($method, $request, $expectedResponse, $live = true, $testMode = false)
    {
        // historyContainer here is passed by reference.  Later
        // we can inspect it to see history.
        $historyContainer = [];

        // This is kind of convoluted but gets it done.  Welcome
        // to modularization without convenience methods :)
        $historyMiddleware = Middleware::history($historyContainer);
        $mockBody = Psr7\stream_for(http_build_query($expectedResponse));
        $mockResponse = new Response(200, [], $mockBody);
        $mockHandler = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($historyMiddleware);

        // Create new ConvergeApi object
        $convergeApi = new \markroland\Converge\ConvergeApi(
            $this->merchantId,
            $this->userId,
            $this->pin,
            $live
        );

        $convergeApi->setHandler($handlerStack);

        $response = $convergeApi->$method($request);

        $this->assertNotFalse($response, "Should not have gotten a false response back");

        $this->assertSame($expectedResponse, $response, "Should have received the expected response");
        $this->assertSame(1, count($historyContainer), "Should have had an outgoing request");
        $transaction = $historyContainer[0];

        // Mix in 
        $expectedRequest = array_merge($request, [
            'ssl_merchant_id' => $this->merchantId,
            'ssl_user_id' => $this->userId,
            'ssl_pin' => $this->pin,
            'ssl_show_form' => 'false',
            'ssl_result_format' => 'ascii',
            'ssl_test_mode' => $testMode ? 'true' : 'false',
            'ssl_transaction_type' => $method
        ]);

        parse_str((string)$transaction['request']->getBody(), $request);

        ksort($expectedRequest);
        ksort($request);

        $this->assertSame('POST', $transaction['request']->getMethod());
        $this->assertSame($expectedRequest, $request);
        $this->assertSame(parse_str((string)$transaction['response']->getBody()), parse_str(http_build_query($expectedResponse)));

        $this->assertSame(
            (string)$transaction['request']->getUri(),
            $live ? 'https://www.myvirtualmerchant.com/VirtualMerchant/process.do' : 'https://demo.myvirtualmerchant.com/VirtualMerchantDemo/process.do'
        );
    }

    public function testHttpBuildQuery()
    {
        $this->assertSame(http_build_query(['a' => 'b', 'c' => 'd']), 'a=b&c=d', 'http_build_query should work as expected');
    }

    public function testCCSale()
    {
        $method = 'ccsale';
        $request = array(
            'ssl_amount' => '9.99',
            'ssl_card_number' => '5000300020003003',
            'ssl_cvv2cvc2' => '123',
            'ssl_exp_date' => '1222',
            'ssl_avs_zip' => '37013',
            'ssl_avs_address' => '123 main',
            'ssl_last_name' => 'Smith'
        );

        $expectedResponse = [
            'errorCode' => '4025'
        ];

        $this->_test($method, $request, $expectedResponse);
    }

    public function testCCSaleDemo()
    {
        $method = 'ccsale';
        $request = array(
            'ssl_amount' => '9.99',
            'ssl_card_number' => '5000300020003003',
            'ssl_cvv2cvc2' => '123',
            'ssl_exp_date' => '1222',
            'ssl_avs_zip' => '37013',
            'ssl_avs_address' => '123 main',
            'ssl_last_name' => 'Smith',
            'ssl_test_mode' => false
        );

        $expectedResponse = [
            'errorCode' => '4025'
        ];

        $this->_test($method, $request, $expectedResponse, true, false);
    }

    public function testCCSaleTestDemo()
    {
        $method = 'ccsale';
        $request = array(
            'ssl_amount' => '9.99',
            'ssl_card_number' => '5000300020003003',
            'ssl_cvv2cvc2' => '123',
            'ssl_exp_date' => '1222',
            'ssl_avs_zip' => '37013',
            'ssl_avs_address' => '123 main',
            'ssl_last_name' => 'Smith',
            'ssl_test_mode' => true
        );

        $expectedResponse = [
            'errorCode' => '4025'
        ];

        $this->_test($method, $request, $expectedResponse, true, true);
    }

    public function testCCAddInstall()
    {
        $method = 'ccaddinstall';

        $request = array(
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
            'ssl_billing_cycle' => 'MONTHLY',
            'ssl_test_mode' => false
        );

        $expectedResponse = [
            'errorCode' => '4025'
        ];

        $this->_test($method, $request, $expectedResponse);
    }
}
