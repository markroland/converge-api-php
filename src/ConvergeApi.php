<?php

namespace markroland\Converge;

/**
 *
 * A PHP class that acts as wrapper for the Elavon Converge API
 *
 * @author Mark Roland
 * @copyright 2014 Mark Roland
 * @license http://opensource.org/licenses/MIT
 * @link http://github.com/markroland/converge-api-php
 *
 **/
class ConvergeApi
{

    /**
     * Merchant ID
     * @var string
     */
    private $merchant_id = '';

    /**
     * User ID
     * @var string
     */
    private $user_id = '';

    /**
     * Pin
     * @var string
     */
    private $pin = '';

    /**
     * API Live mode
     * @var boolean
     */
    private $live = true;

    /**
     * Insecure mode for old servers
     * @var boolean
     */
    private $insecure = false;

    /**
     * Guzzle handler for mocks/etc
     * @var GuzzleHttp\Handler\MockHandler
     */
    private $handler = null;

    /**
     * A variable to hold debugging information
     * @var array
     */
    public $debug = array();

    /**
     * Class constructor
     *
     * @param string $merchant_id Merchant ID
     * @param string $user_id User ID
     * @param string $pin PIN
     * @param boolean $live True to use the Live server, false to use the Demo server
     * @return null
     **/
    public function __construct($merchant_id, $user_id, $pin, $live = true, $insecure = false)
    {
        $this->merchant_id = $merchant_id;
        $this->user_id = $user_id;
        $this->pin = $pin;
        $this->live = $live;
        $this->insecure = $insecure;
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    private function httpRequest($api_method, $data) {
        // Standard data
        $data['ssl_merchant_id'] = $this->merchant_id;
        $data['ssl_user_id'] = $this->user_id;
        $data['ssl_pin'] = $this->pin;
        $data['ssl_show_form'] = 'false';
        $data['ssl_result_format'] = 'ascii';

        if (!empty($data['ssl_test_mode']) && !is_string($data['ssl_test_mode'])) {
            $data['ssl_test_mode'] = $data['ssl_test_mode'] ? 'true' : 'false';
        } else {
            $data['ssl_test_mode'] = 'false';
        }

        $guzzleOptions = ['defaults' => []];

        // I haven't tested this but Goooogling gives:
        // http://stackoverflow.com/questions/28066409/how-to-ignore-invalid-ssl-certificate-errors-in-guzzle-5
        $guzzleOptions['defaults']['verify'] = !$this->insecure;

        if ($this->handler) {
            $guzzleOptions['handler'] = $this->handler;
        }

        // Set request
        if ($this->live) {
            $request_url = 'https://api.convergepay.com/VirtualMerchant/process.do';
        } else {
            $request_url = 'https://api.demo.convergepay.com/VirtualMerchantDemo/process.do';
        }

        $body = http_build_query($data);

        // Debugging output
        $this->debug = array();
        $this->debug['Request URL'] = $request_url;
        $this->debug['SSL Mode'] = $this->insecure ? 'WARNING: VERIFICATION DISABLED': 'Verification enabled';
        $this->debug['Posted Data'] = $data ? $body : null;

        try {
            $client = new \GuzzleHttp\Client($guzzleOptions);
            $response = $client->request('POST', $request_url, [
                'form_params' => $data,
            ]);
        } catch (\Exception $e) {
            $this->debug['Exception'] = $e;
            return null;
        }

        $responseBody = (string)$response->getBody();

        $this->debug['Response Status Code'] = $response->getStatusCode();
        $this->debug['Response Reason Phrase'] = $response->getReasonPhrase();
        $this->debug['Response Protocol Version'] = $response->getProtocolVersion();
        $this->debug['Response Headers'] = $response->getHeaders();
        $this->debug['Response Body'] = $responseBody;
        return $responseBody;
    }

    /**
     * Send a HTTP request to the API
     *
     * @param string $api_method The API method to be called
     * @param array $data Any data to be sent to the API
     * @param string $multisplit The key in the response body to use to break multiple records on
     * @param string $multikey The key in the response array to put the multiple results
     * @return array
     **/
    private function sendRequest($api_method, $data, $multisplit = NULL, $multikey = NULL)
    {
        $responseBody = $this->httpRequest($api_method, $data);

        // Parse and return
        return $this->parseAsciiResponse($responseBody, $multisplit, $multikey);
    }

    /**
     * Parse an ASCII response
     * @param string $ascii_string An ASCII (plaintext) Response
     * @param string $multisplit The key in the response body to use to break multiple records on
     * @param string $multikey The key in the response array to put the multiple results
     * @return array
     *         If $multisplit is NULL, then response will be key value pairs like:
     *         array(
     *             'ssl_result' => '0',
     *             'ssl_result_message' => 'APPROVAL',
     *             'ssl_txn_id' => '1234'
     *         )
     *         from an input of:
     *         ssl_result=0
     *         ssl_result_message=APPROVAL
     *         ssl_txn_id=1234
     *
     *         If $multisplit is not NULL, then response will be key value pairs with an extra
     *         entry for $multikey, like when $multisplit="ssl_txn_id" and $multikey="transactions":
     *         array(
     *             'ssl_txn_count' => 2,
     *             "transactions" => array(
     *                 array("ssl_txn_id" => "1234", "ssl_amount" => "0.37"),
     *                 array("ssl_txn_id" => "5678", "ssl_amount" => "1.22")
     *             )
     *         )
     *         from an input of:
     *         ssl_txn_count=2
     *         ssl_txn_id=1234
     *         ssl_amount=0.37
     *         ssl_txn_id=5678
     *         ssl_amount=1.22
     **/
    private function parseAsciiResponse($ascii_string, $multisplit = NULL, $multikey = NULL)
    {
        $data = array();
        if ($multisplit !== NULL) {
            $data[$multikey] = array();
        }
        $lines = explode("\n", $ascii_string);
        $record = NULL;
        $isCapturingMulti = false;

        if (count($lines)) {
            foreach ($lines as $line) {
                if ($kvp = explode('=', $line, 2)) {
                    if (count($kvp) != 2) {
                        continue;
                    }
                    // if the key matches the $multisplit key to split on
                    // and we were already parsing a record, push onto
                    // the $multikey in data
                    if ($multisplit !== NULL && $kvp[0] === $multisplit) {
                        // once we start capturing records, we only have
                        // individual key value pairs that are transaction specific
                        $isCapturingMulti = true;
                        // if we were building a previous record, push it
                        if ($record !== NULL) {
                            $data[$multikey][] = $record;
                        }
                        // initialize record to empty
                        $record = array();
                    }

                    // if we are capturing multi, populate the record
                    // even with the key we split on
                    if ($isCapturingMulti) {
                        $record[$kvp[0]] = $kvp[1];
                    }
                    // if we are not capturing multiple records yet
                    // in the response, we have a response-wide key/value pair
                    // so store at the top level of the data
                    else {
                        $data[$kvp[0]] = $kvp[1];
                    }
                }
            }
            // after we are done capturing, if we captured multiple records
            // the last one will not have been added on yet
            if ($isCapturingMulti && $record !== NULL) {
                $data[$multikey][] = $record;
                $record = NULL;
            }
        }
        return $data;
    }

    /**
     * Submit "ccsale" request
     * @param array $parameters Input parameters
     * @return array Response from Converge
     **/
    public function ccsale(array $parameters = array())
    {
        $parameters['ssl_transaction_type'] = 'ccsale';
        return $this->sendRequest('ccsale', $parameters);
    }

    /**
     * Submit "ccaddinstall" request
     * @param array $parameters Input parameters
     * @return array Response from Converge
     **/
    public function ccaddinstall(array $parameters = array())
    {
        $parameters['ssl_transaction_type'] = 'ccaddinstall';
        return $this->sendRequest('ccaddinstall', $parameters);
    }

    /**
     * Submit "ccaddrecurring" request
     * @param array $parameters Input parameters
     * @return array Response from Converge
     **/
    public function ccaddrecurring(array $parameters = array())
    {
        $parameters['ssl_transaction_type'] = 'ccaddrecurring';
        return $this->sendRequest('ccaddrecurring', $parameters);
    }

    /**
     * Submit "ccupdaterecurring" request
     * @param array $parameters Input parameters
     * @return array Response from Converge
     **/
    public function ccupdaterecurring(array $parameters = array())
    {
        $parameters['ssl_transaction_type'] = 'ccupdaterecurring';
        return $this->sendRequest('ccupdaterecurring', $parameters);
    }

    /**
     * Submit "ccdeleterecurring" request
     * @param array $parameters Input parameters
     * @return array Response from Converge
     **/
    public function ccdeleterecurring(array $parameters = array())
    {
        $parameters['ssl_transaction_type'] = 'ccdeleterecurring';
        return $this->sendRequest('ccdeleterecurring', $parameters);
    }

    /**
     * Submit "ccresurringsale" request
     * @param array $parameters Input parameters
     * @return array Response from Converge
     **/
    public function ccresurringsale(array $parameters = array())
    {
        $parameters['ssl_transaction_type'] = 'ccresurringsale';
        return $this->sendRequest('ccresurringsale', $parameters);
    }

    /**
     * Submit "txnquery" request
     * @param array $parameters Input parameters
     * @return array Response from Converge
     **/
    public function txnquery(array $parameters = array())
    {
        $parameters['ssl_transaction_type'] = 'txnquery';
        return $this->sendRequest('txnquery', $parameters, 'ssl_txn_id', 'transactions');
    }

    /**
     * Submit "ccauthonly" request
     * @param array $parameters Input parameters
     * @return array Response from Converge
     **/
    public function ccauthonly(array $parameters = array())
    {
        $parameters['ssl_transaction_type'] = 'ccauthonly';
        return $this->sendRequest('ccauthonly', $parameters);
    }
}
