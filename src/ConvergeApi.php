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

    /**
     * Send a HTTP request to the API
     *
     * @param string $api_method The API method to be called
     * @param array $data Any data to be sent to the API
     * @return string
     **/
    private function sendRequest($api_method, $data)
    {

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
                'headers' => [],
                'body' => $body,
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

        // Parse and return
        return $this->parseAsciiResponse($responseBody);
    }

    /**
     * Parse an ASCII response
     * @param string $ascii_string An ASCII (plaintext) Response
     * @return array
     **/
    private function parseAsciiResponse($ascii_string)
    {
        $data = array();
        $lines = explode("\n", $ascii_string);

        if (count($lines)) {
            foreach ($lines as $line) {
                if ($kvp = explode('=', $line, 2)) {
                    if (count($kvp) != 2) {
                        continue;
                    }

                    $data[$kvp[0]] = $kvp[1];
                }
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
}
