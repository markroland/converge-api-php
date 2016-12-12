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

    public function _test($method, $request, $expectedResponse, $expectedResponseBody, $live = true, $testMode = false)
    {
        // historyContainer here is passed by reference.  Later
        // we can inspect it to see history.
        $historyContainer = [];

        // This is kind of convoluted but gets it done.  Welcome
        // to modularization without convenience methods :)
        $historyMiddleware = Middleware::history($historyContainer);

        $mockBody = $expectedResponseBody;
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

        ksort($expectedResponse);
        ksort($response);

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

        $this->assertSame('POST', $transaction['request']->getMethod(), "Should have a method of POST");
        $this->assertSame($expectedRequest, $request, "Should have sent the expected request");
        $this->assertSame((string)$transaction['response']->getBody(), $expectedResponseBody, "Should have contained the expected response body");

        $this->assertSame(
            (string)$transaction['request']->getUri(),
            $live ? 'https://api.convergepay.com/VirtualMerchant/process.do' : 'https://api.demo.convergepay.com/VirtualMerchantDemo/process.do',
            "Should have sent a request to the expected Uri"
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
            'ssl_result' => '0',
            'ssl_result_message' => 'APPROVAL',
            'ssl_txn_id' => '1234'
        ];

        $expectedResponseBody = $this->_transactionToResponseBody($expectedResponse);

        $this->_test($method, $request, $expectedResponse, $expectedResponseBody);
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

        $expectedResponseBody = $this->_transactionToResponseBody($expectedResponse);

        $this->_test($method, $request, $expectedResponse, $expectedResponseBody, true, false);
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

        $expectedResponseBody = $this->_transactionToResponseBody($expectedResponse);

        $this->_test($method, $request, $expectedResponse, $expectedResponseBody, true, true);
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

        $expectedResponseBody = $this->_transactionToResponseBody($expectedResponse);

        $this->_test($method, $request, $expectedResponse, $expectedResponseBody);
    }

    private function _transactionToResponseBody($transaction) {
        return implode("\n", array_map(function ($v, $k) { return sprintf("%s=%s", $k, $v); },
            $transaction,
            array_keys($transaction)
        ));
    }

    public function testTxnquery()
    {
        $method = 'txnquery';

        $transactionId = 1234;

        $request = array(
            'ssl_txn_id' => "$transactionId"
        );

        $expectedResponse = array(
            "ssl_txn_count" => '1',
            "transactions" => array(
                array(
                    "ssl_txn_id" => "$transactionId",
                    "ssl_user_id" => "my_user",
                    "ssl_trans_status" => "STL",
                    "ssl_card_type" => "CREDITCARD",
                    "ssl_transaction_type" => "SALE",
                    "ssl_txn_time" => "07/09/2013 02:29:13 PM",
                    "ssl_first_name" => "John",
                    "ssl_last_name" => "Doe",
                    "ssl_card_number" => "00**********0000",
                    "ssl_exp_date" => "1215",
                    "ssl_entry_mode" => "K",
                    "ssl_avs_response" => "",
                    "ssl_cvv2_response" => "",
                    "ssl_amount" => '.37',
                    "ssl_invoice_number" => "",
                    "ssl_result_message" => "APPROVAL",
                    "ssl_approval_code" => "N29032"
                )
            )
        );

        $expectedResponseBody = "ssl_txn_count=1";      
        foreach ($expectedResponse["transactions"] as $t) {
            $expectedResponseBody .= "\n" . $this->_transactionToResponseBody($t); 
        }  

        $this->_test($method, $request, $expectedResponse, $expectedResponseBody);
    }

    public function testTxnqueryMulti()
    {
        $method = 'txnquery';

        $cardNumber = '1234567890123456';

        $request = array(
            'ssl_card_number' => "$cardNumber"
        );

        $transactionId0 = 1234;
        $transactionId1 = 5678;

        $expectedResponse = array(
            "ssl_txn_count" => '2',
            "transactions" => array(
                array(
                    "ssl_txn_id" => "$transactionId0",
                    "ssl_user_id" => "my_user",
                    "ssl_trans_status" => "STL",
                    "ssl_card_type" => "CREDITCARD",
                    "ssl_transaction_type" => "SALE",
                    "ssl_txn_time" => "07/09/2013 02:29:13 PM",
                    "ssl_first_name" => "John",
                    "ssl_last_name" => "Doe",
                    "ssl_card_number" => "00**********0000",
                    "ssl_exp_date" => "1215",
                    "ssl_entry_mode" => "K",
                    "ssl_avs_response" => "",
                    "ssl_cvv2_response" => "",
                    "ssl_amount" => '.37',
                    "ssl_invoice_number" => "",
                    "ssl_result_message" => "APPROVAL",
                    "ssl_approval_code" => "N29032"                    
                ),
                array(
                    "ssl_txn_id" => "$transactionId1",
                    "ssl_user_id" => "my_user",
                    "ssl_trans_status" => "STL",
                    "ssl_card_type" => "CREDITCARD",
                    "ssl_transaction_type" => "SALE",
                    "ssl_txn_time" => "07/09/2013 02:29:13 PM",
                    "ssl_first_name" => "John",
                    "ssl_last_name" => "Doe",
                    "ssl_card_number" => "00**********0000",
                    "ssl_exp_date" => "1215",
                    "ssl_entry_mode" => "K",
                    "ssl_avs_response" => "",
                    "ssl_cvv2_response" => "",
                    "ssl_amount" => '.37',
                    "ssl_invoice_number" => "",
                    "ssl_result_message" => "APPROVAL",
                    "ssl_approval_code" => "N29032"                    
                ),
            )

        );

        $expectedResponseBody = "ssl_txn_count=2";
        foreach ($expectedResponse["transactions"] as $t) {
            $expectedResponseBody .= "\n" . $this->_transactionToResponseBody($t); 
        }      

        $this->_test($method, $request, $expectedResponse, $expectedResponseBody);
    }    
	
    public function testCCAuthOnly()
    {
        $method = 'ccauthonly';
        $request = array(
            'ssl_amount' => '9.99',
            'ssl_card_number' => '5000300020003003',
            'ssl_cvv2cvc2' => '123',
            'ssl_exp_date' => '1222',
            'ssl_avs_zip' => '37013',
            'ssl_avs_address' => '123 main',
			'ssl_first_name' => 'Adam',
            'ssl_last_name' => 'Smith'
        );

        $expectedResponse = [
            'ssl_result' => '0',
            'ssl_result_message' => 'APPROVAL',
            'ssl_txn_id' => '1234'
        ];

        $expectedResponseBody = $this->_transactionToResponseBody($expectedResponse);

        $this->_test($method, $request, $expectedResponse, $expectedResponseBody);
    }   
}
