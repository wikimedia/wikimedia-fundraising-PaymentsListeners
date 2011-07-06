<?php

require_once dirname(__FILE__) . '/../stompPFPPendingProcessorSA.php';
require_once 'ActiveMQStompTest.class.php';

/**
 * Test and object adapter class for StompPFPPendingProcessorSA.
 *
 * For this test file to run, you must boostrap phpUnit with the following file:
 * /fundraising-misc/testing_resources/phpUnit_bootstrap.php
 *
 * @author: Katie Horn <khorn@wikimedia.org>
 */
$logdata = array();

class testPPPlogging extends StompPFPPendingProcessorSA {

	public $logdata;

	public function __construct($config) {
		require_once( $config['stomp_path'] );
		$this->config_base = $config;
		$this->load_config_options($this->config_base);
	}

	/**
	 * overrides the log function in the parent object so we can access the
	 * logged data with the test class. Logs out to the global $logdata
	 * variable.
	 *
	 * @param $message String the message to be logged
	 * @param $level Int Log level, defualt of LOG_LEVEL_INFO
	 * @return null
	 */
	public function log($message, $level=LOG_LEVEL_INFO) {
		global $logdata;
		if ($this->log_level >= $level) {
			$out = date('c') . ": " . $level . " : " . $message . "\n";
			$logdata[] = $out;
		}
	}

	/**
	 * This function is used for faking a response from PayfloPro.
	 *
	 * @param $code Int The numeric code we want to fake, for all the items
	 * we're going to try to handle with the parent's execute() function.
	 * @return null
	 */
	public function setPFPTestMode($code = ""){
		$this->test_mode = true;
		if ($code != ""){
			$this->test_code = $code;
		}
		unset ($this->pfp_user_id);  //ARGHARGHARGH. That is all.
		$this->refreshConfig(array('pfp_user_id' => ''));
	}

	/**
	 * Refreshes the config options on the parent object, for testing different
	 * configurations (connection destinations, bad credentials, etc)
	 *
	 * @param $config Array Any key/value pairs that you want to override.
	 * @return null
	 */
	public function refreshConfig($config = array()) {
		$this->config_current = array_merge($this->config_base, $config);
		$this->load_config_options($this->config_current);
	}

	/**
	 * Sets what ought to be a good stomp connection
	 *
	 * @return boolean the result of the parent object's set_stomp_connection()
	 */
	public function setGoodStompConnection() {
		$this->refreshConfig();
		return $this->set_stomp_connection();
	}

	/**
	 * Sets what ought to be a bad stomp connection, by setting the uri to
	 * "CompleteNonsense".
	 *
	 * @return boolean the result of the parent object's set_stomp_connection()
	 */
	public function setBadStompConnection() {
		$this->refreshConfig(array('activemq_stomp_uri' => 'CompleteNonsense'));
		return $this->set_stomp_connection();
	}

	/**
	 * Attempts to fetch a message from a queue that should be known as empty.
	 *
	 * @return mixed the result of the parent object's fetch_message(): Raw message, or False
	 */
	public function getFetchNothing() {
		$this->setGoodStompConnection();
		$message = $this->fetch_message('/queue/test_empty');
		return $message;
	}

	/**
	 * Attempts to fetch a message from the activemq_pending_queue as predefined
	 * either in the config.ini, or overridden on a refreshConfig()
	 *
	 * @return mixed the result of the parent object's fetch_message(): Raw message, or False
	 */
	public function getFetchFromPending() {
		$this->setGoodStompConnection();
		$message = $this->fetch_message($this->activemq_pending_queue);
		return $message;
	}

	/**
	 * Attempts to ack a message
	 * either in the config.ini, or overridden on a refreshConfig()
	 *
	 * @param $message Object The raw stomp message
	 * @return bool the result of the parent object's ack_message()
	 */
	public function ack_message($message) {
		return parent::ack_message($message);
	}

	/**
	 * Attempts to add a message to the pending queue.
	 *
	 * @param $message String The JSON-encoded message body to send to the queue.
	 * @return boolean the result of the parent object's queue_message()
	 */
	public function addMessageToPending($message) {
		$this->setGoodStompConnection();
		return $this->queue_message($this->activemq_pending_queue, $message);
	}

	/**
	 * Attempts to retrieve the PayfloPro status of a particular transaction.
	 *
	 * @param $transaction_id String The transaction ID of the item in question.
	 * @return Int the decoded transaction status.
	 */
	public function getPayflowProStatus($transaction_id) {
		$status = $this->fetch_payflow_transaction_status($transaction_id);
		$decoded = $this->parse_payflow_transaction_status($status);
		return $decoded;
	}

	/**
	 * fetch message public adapter
	 *
	 * @param $queue String the queue from which to fetch a message
	 * @return mixed Either the stomp frame from a successful fetch, or false
	 */
	public function fetch_message($queue) {
		return parent::fetch_message($queue);
	}

	/**
	 * queue_message public adapter
	 *
	 * @param $queue String the detination queue
	 * @param $message String JSON formatted message body
	 * @param $options Array Any queueing options we want to test. Parent
	 * defaults this to persistent = true
	 * @return bool the result from the parent's queue_message function
	 */
	public function queue_message($queue, $message, $options = null) {
		return parent::queue_message($queue, $message, $options);
	}


	/**
	 * Insert a dummy ppp transaction that we can immediately pull and re-check
	 * the status of.
	 *
	 * @param $input Array Override key/value pairs for the $queryArray
	 * @return string The key/value formatted response string from pfp
	 */
	public function inject_payflow_test_transaction($input = array()) {
		// create payflow query string, include string lengths
		$queryArray = array(
			'TRXTYPE' => 'S', //not C. C is for "refund".
			'TENDER' => 'C',
			'USER' => $this->pfp_user_id, //$payflow_data['user'],
			'VENDOR' => $this->pfp_vendor_id, //$payflow_data['vendor'],
			'PARTNER' => $this->pfp_partner_id, //$payflow_data['partner'],
			'PWD' => $this->pfp_password, //$payflow_data['password'],
			'VERBOSITY' => 'MEDIUM',
			'AMT' => '42.50',
			'ACCT' => '5555555555554444', //card number
			'EXPDATE' => '1012', //cc expiration date. MMYY
			'COMMENT1' => 'TEST TEST TEST',
			'FIRSTNAME' => 'John',
			'LASTNAME' => 'Jones',
			'STREET' => '123 Main St.',
			'CITY' => 'San Jose',
			'STATE' => 'CA',
			'ZIP' => '123451234',
			'BILLTOCOUNTRY' => 'US',
			//'CVV2' => '123',
			//'CUSTIP' => '0.0.0.0',
		);

		$queryArray = array_merge($queryArray, $input);

		// format the query string for PayflowPro
		foreach ($queryArray as $name => $value) {
			if ($name != 'PWD'){
				$value = rawurlencode($value);
			}
			$query[] = $name . '[' . strlen($value) . ']=' . $value;
		}
		$payflow_query = implode('&', $query);

		// assign header data necessary for the curl_setopt() function
 		$order_id = date('ymdH') . rand(1000, 9999); //why?
		if(array_key_exists('HTTP_USER_AGENT', $_SERVER)){
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$user_agent = false;
		}
		$headers[] = 'Content-Type: text/namevalue';
		$headers[] = 'Content-Length : ' . strlen($payflow_query);
		$headers[] = 'X-VPS-Client-Timeout: 45';
		$headers[] = 'X-VPS-Request-ID:' . $order_id;
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->pfp_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($user_agent !== false){
			curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		}
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payflow_query);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_POST, 1);

		// As suggested in the PayPal developer forum sample code, try more than once to get a response
		// in case there is a general network issue
		for ($i = 1; $i <= 3; $i++) {
			$response = curl_exec($ch);
			$headers = curl_getinfo($ch);

			if ($headers['http_code'] != 200 && $headers['http_code'] != 403) {
				sleep(5);
			} elseif ($headers['http_code'] == 200 || $headers['http_code'] == 403) {
				break;
			}
		}

		if ($headers['http_code'] != 200) {
			curl_close($ch);
			exit(1);
		}

		curl_close($ch);

		return $this->pfp_response_to_array($response);
		//TODO: Error more nicely here. Or, y'know: At all.
	}

	/**
	 * Basically just explodes twice. In a good way.
	 * (Turns a querystring into a key/value array)
	 *
	 * @param $response String The raw string returned from pfp
	 * @return string The key/value formatted response string 
	 */
	public function pfp_response_to_array($response){
		//double explode! 
		$response = strstr($response, "RESULT");
		$response = explode('&', $response);
		foreach($response as $thing=>$otherthing){
			$temp = explode('=', $otherthing);
			if(sizeof($temp) > 1){
				$response[$temp[0]] = $temp[1];
				unset($response[$thing]);
			}
		}
		return $response;
	}

}

class StompPFPPendingProcessorSATest extends ActiveMQStompTest {

	/**
	 * @var StompPFPPendingProcessorSA
	 *
	 * @TODO: Additional Tests I want:
	 *	Test to see what happens if you overrun the batch size on execute, and call it multiple times.
	 *	Test to make sure that duplicate messages in the pending queue collapse properly
	 */
	protected $processor;
	protected $config_base;
	protected $config_current;

	protected $ignore = array(
		//add 'testname' => 'reason' in here, to avoid running (long/annoying/otherwise problematic) tests during development.
	);
	protected $singleTest = ""; //set this if you only want to run one test in the pile.


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
	protected function setUp() {
		// set some configuration variables (for more info, check in PaypalIPNListener.php
		$this->config_base = array(
			'output_handle' => '',
			'pfp_url' => '',
			'pfp_partner_id' => '',
			'pfp_vendor_id' => '',
			'pfp_user_id' => '',
			'pfp_password' => '',
			'activemq_stomp_uri' => '',
			'activemq_pending_queue' => '',
			'activemq_confirmed_queue' => '',
			'batch_size' => 20,
			'log_level' => 2,
			'stomp_path' => '');

		// ActiveMQStompTest wants a base message. 
		$this->define_base_message(array(
			'op' => 'Insert into queue',
			'submit1' => 'Insert into queue',
			'queue' => '/queue/test_donations',
			'contribution_tracking_id' => 'unittest1',
			'optout' => '0',
			'anonymous' => '0',
			'comment' => 'Generated with Unit Testing',
			'utm_source' => '112358134',
			'utm_medium' => '112358134',
			'utm_campaign' => 'unittesting',
			'language' => '2',
			'referrer' => 'http://example.com/2115175271',
			'email' => '2022397297@example.com',
			'first_name' => '287390372',
			'middle_name' => '1523362659',
			'last_name' => '44629465',
			'street_address' => '500653283',
			'supplemental_address_1' => '',
			'city' => 'San Francisco',
			'state_province' => 'CA',
			'country' => 'USA',
			'countryID' => 'US',
			'postal_code' => '60535',
			'gateway' => 'unit_test',
			'gateway_txn_id' => '1604643431',
			'response' => '900646333',
			'currency' => 'USD',
			'original_currency' => 'USD',
			'original_gross' => '23.50',
			'fee' => '0',
			'gross' => '23.50',
			'net' => '23.50',
			'date' => 'Wed, 22 Jun 2011 14:43:06 -0700',
			'submit2' => 'Insert into queue',
			'form_build_id' => 'form-1cb52594a56da97a1bda13cc2b1517eb',
			'form_token' => '0e7843c070bed0b6beb6bd652f719106',
			'form_id' => 'pending_processor_unit_test',
		));
		$this->config_base = array_merge($this->config_base, $this->getConfig());
		$this->processor = new testPPPlogging($this->config_base);
	}


    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
		unset($this->processor);
    }

	/**
	 * Re-initializes the processor object
	 *
	 * @return null
	 */
	protected function reInitProcessor(){
		unset($this->processor);
		$this->processor = new testPPPlogging($this->config_base);
	}

	/**
	 * Reads the config.ini, returns it as an array.
	 *
	 * @return Array The key/value array formatted config.ini
	 */
	protected function getConfig(){
		if (!file_exists("config.ini")){
			$this->fail("You need a config.ini file. Please make yours.");
		}
		$ini_config = parse_ini_file("config.ini");
		return $ini_config;
	}

	/**
	 * Establishes a stomp connection - Overridden from ActiveMQStompTest
	 *
	 * @return Boolean True on good connection, False on failure
	 */
	protected function establishStompConnection(){
		return $this->processor->setGoodStompConnection();
	}

	/**
	 * Acknowledge consumption of a message
	 *
	 * @param $message Object StompFrame of the message you wish to acknowledge.
	 * @return mixed Result on success, FALSE on failure. However, this will almost always be "1", because ack is asynchronous.
	 */
	protected function ack_message($message){
		return $this->processor->ack_message($message);
	}

	/**
	 * Fetches the next message from the queue
	 *
	 * @param $queue String Queue from which you will be fetching the next message
	 * @return mixed The entire message in Stomp Frame format, or empty
	 */
	protected function fetch_message($queue){
		return $this->processor->fetch_message($queue);
	}

	/**
	 * add a message to a queue
	 *
	 * @param $queue String Destination queue for the message
	 * @param $message String(JSON) Message to add to the destination queue
	 * @return mixed raw message (Stomp_Frame object) from Stomp client or False if no msg present
	 */
	protected function queue_message($queue, $message){
		return $this->processor->queue_message($queue, $message, null);
	}

	/**
	 * Test the log helper functions in the processor object. If those don't
	 * work, most of the rest of this falls down pretty quick.
	 *
	 * @return null
	 */
	public function testLogHelpers() {
		$this->processor->log("Testing the log search function");
		if (!$this->checkLog("function", true)) {
			$this->markTestIncomplete("checkLog isn't working.");
		}
		if ($this->checkLog("function")) {
			$this->markTestIncomplete("checkLog isn't resetting properly.");
		}
	}

	/**
	 * Tests the logging that happens when we set good and bad stomp connections. 
	 *
	 * @return null
	 */
	public function testStompConnectionLogs() {
		//test that a bad connection is properly logged
		$this->assertFalse($this->processor->setBadStompConnection(), "Bad Stomp Connection should return false");
		$this->assertTrue($this->checkLog("Stomp connection failed", true), "Bad Stomp Connection was not logged");

		//test that a good connection is properly logged
		$this->assertTrue($this->processor->setGoodStompConnection(), "Good Stomp Connection should return true");
		$this->assertTrue($this->checkLog("Successfully connected to Stomp listener", true), "Bad Stomp Connection was not logged");
	}

	/**
	 * Tests the logging that happens when we construct and bootstrap, and when
	 * we destroy the processor object.
	 *
	 * @return null
	 */
	public function testDestructConstructLogs() {
		//destroy first. Then reconstruct. Don't assume.
		unset($this->processor);
		$checkLogItems = array();
		$this->logCheckArrayAdd("Cleaning up stomp connection...", "Stomp Cleanup attempt not logged.", $checkLogItems);
		$this->logCheckArrayAdd("Stomp connection cleaned up", "Stomp cleanup not logged.", $checkLogItems);
		$this->logCheckArrayAdd("Exiting gracefully", "Graceful Exit not logged.", $checkLogItems);
		$this->assertLogEntries($checkLogItems, true);


		$this->processor = new testPPPlogging($this->config_base);
		//test that the bootsrtapping mess got logged
		$checkLogItems = array();
		foreach ($this->config_base as $thing => $otherthing) {
			if (trim($otherthing) != '') {
				if ($thing == 'pfp_user_id' || $thing == 'pfp_password'){
					$this->logCheckArrayAdd("$thing =  [set, but not logged]", "Setting $thing was logged! Please remove this sensitive information.", $checkLogItems);
				} else {
					$this->logCheckArrayAdd("$thing =  $otherthing", "Setting $thing was not logged.", $checkLogItems);
				}
			}
		}
		$this->assertLogEntries($checkLogItems, true);
	}

	/**
	 * Tests our ability to fetch transactions from ActiveMQ, and the logging 
	 * that goes with it.
	 *
	 * @return null
	 */
	public function testFetchTransactionsAndLogs() {
		$message = $this->processor->getFetchNothing();

		$checkLogItems = array();
		$this->logCheckArrayAdd("Attempting to connect to queue at", "Connection attempt not logged.", $checkLogItems);
		$this->logCheckArrayAdd("Attempting to pull queued item", "Queued item pull attempt not logged.", $checkLogItems);
		$this->logCheckArrayAdd("No message found", "No message found not logged.", $checkLogItems);

		$this->assertLogEntries($checkLogItems, true);

		$testmessage = json_encode(array(
				'first' => 'This',
				'second' => 'is',
				'third' => 'my',
				'fourth' => 'message!',
			));

		$this->assertEmptyQueueWithAck($this->processor->activemq_pending_queue);

		$this->processor->addMessageToPending($testmessage);
		$this->assertTrue($this->checkLog("Attempting to queue message..."), "Message Queue Attempt was not logged");
		$this->assertTrue($this->checkLog("Result of queuing message:", true), "Message Queue Result was not logged");

		$this->processor->setGoodStompConnection();
		$message = $this->processor->getFetchFromPending();
		//check the logs
		$this->logCheckArrayRemove("No message found", $checkLogItems);
		$this->logCheckArrayAdd(print_r($testmessage, true), "Test Message Not Returned", $checkLogItems);

		$this->assertLogEntries($checkLogItems, true);
	}

	/**
	 * Tests our ability to retrieve information from Payflow Pro, and the 
	 * logging that goes with it. 
	 *
	 * @return null
	 */
	public function testPayflowProConnectionAndLogging() {
		//testing with bad creds and a bad transaction id
		$transaction_id = "nonsense";
		$creds = array(
			'pfp_user_id' => 'mrMcNonsense',
			'pfp_password' => 'nothing',
			'pfp_vendor_id' => 'splunge',
			'pfp_partner_id' => 'urr'
		);
		$this->processor->refreshConfig($creds);
		$decoded = $this->processor->getPayflowProStatus($transaction_id, $creds);

		$checkLogItems = array();
		$this->logCheckArrayAdd("Transaction ID: $transaction_id", "Transaction ID not logged.", $checkLogItems);
		$this->logCheckArrayAdd("PayflowPro query array:", "PayflowPro query array not logged.", $checkLogItems);
		$this->logCheckArrayAdd("PayflowPro query array (formatted):", "Formatted PayflowPro query array not logged.", $checkLogItems);
		$this->logCheckArrayAdd("to connect to PayflowPro...", "Attempt to connect PFP not logged.", $checkLogItems);
		$this->logCheckArrayAdd("Succesfully connected to PayflowPro", "Connection to PFP not logged.", $checkLogItems);
		$this->logCheckArrayAdd("PayflowPro reported status:", "PFP status not logged.", $checkLogItems);


		$this->logCheckArrayAdd("PayflowPro RESULT string:", "PFP result string not logged.", $checkLogItems);
		$this->logCheckArrayAdd("PayflowPro returned NO result code!", "PFP failure to return code not logged.", $checkLogItems);

		$this->assertLogEntries($checkLogItems, true);
		
		$this->logCheckArrayRemove("Transaction ID: $transaction_id", $checkLogItems);
		$this->logCheckArrayRemove("PayflowPro returned NO result code!", $checkLogItems);

		$this->processor->refreshConfig(); //it gets the good creds. :)
		//now, insert a few things, and keep track of what we think they should return.
		$transaction = array(
			'AMT' => '42.50',
			'ACCT' => '378734493671000', //card number
			'EXPDATE' => '1012', //cc expiration date. MMYY
			'COMMENT1' => 'TEST TEST TEST',
			'FIRSTNAME' => 'John',
			'LASTNAME' => 'Jones',
			'STREET' => '123 Main St.',
			'CITY' => 'San Jose',
			'STATE' => 'CA',
			'ZIP' => '123451234',
			'BILLTOCOUNTRY' => 'US',
		); //this should result in an OK.
		$current_expected = '0';

		$response = $this->persistent_add_pfp_transaction($transaction, 5);
		$this->assertTrue($response['RESULT'] == $current_expected, "PayflowPro responded abnormally. (Should be $current_expected)" . print_r($response, true));

		$transaction_id = $response['PNREF'];
		$this->logCheckArrayAdd("Transaction ID: $transaction_id", "Transaction ID not logged.", $checkLogItems);

		$decoded = $this->processor->getPayflowProStatus($transaction_id, $creds);
		$this->assertTrue($decoded == $current_expected, "PayflowPro responded with the wrong status (should be '$current_expected')");
		$this->logCheckArrayAdd("PayflowPro result code: $decoded", "PayflowPro decoded result not logged: $decoded", $checkLogItems);
		$this->assertLogEntries($checkLogItems, true);
		$this->logCheckArrayRemove("Transaction ID: $transaction_id", $checkLogItems);
		$this->logCheckArrayRemove("PayflowPro result code: $decoded", $checkLogItems);


		$transaction['AMT'] = '15002';//this should result in REJECTION. Ooo.
		$transaction['ACCT'] = '378282246310005';

		$current_expected = '12';

		$response = $this->persistent_add_pfp_transaction($transaction, 5);
		$this->assertTrue($response['RESULT'] == $current_expected, "PayflowPro responded abnormally. (Should be $current_expected)" . print_r($response, true));

		$transaction_id = $response['PNREF'];
		$this->logCheckArrayAdd("Transaction ID: $transaction_id", "Transaction ID not logged.", $checkLogItems);

		$decoded = $this->processor->getPayflowProStatus($transaction_id, $creds);
		$this->assertTrue($decoded == $current_expected, "PayflowPro responded with the wrong status (should be '$current_expected')");
		$this->logCheckArrayAdd("PayflowPro result code: $decoded", "PayflowPro decoded result not logged: $decoded", $checkLogItems);
		$this->assertLogEntries($checkLogItems, true);
		$this->logCheckArrayRemove("Transaction ID: $transaction_id", $checkLogItems);
		$this->logCheckArrayRemove("PayflowPro result code: $decoded", $checkLogItems);


		//The following block is useful for testing what the heck Paypal actually returns for its test accounts.
		//I intend to rip this out and do something more useful with it, but for now...

/*		//$amounts = array(10417,15002,15005,15006,15028,15039,10544,10545,10546);
		$amounts = array("42.50");
		$cards = array(
			"378282246310005",	//This gave me a 12!
			"371449635398431",	//0 - //TIMEOUTx4
			"378734493671000",	//0
			"5610591081018250",	//23 - unsupported type?
			"30569309025904",	//25 - not signed up for this tender type
			"38520000023237",	//25
			"6011111111111117", //0 - 1000x1
			"6011000990139424",	//0
			"3530111333300000",	//25
			"3566002020360505",	//25
			"5555555555554444",	//0
			"5105105105105100",	//0//TIMEOUTx6
			"4111111111111111",	//1000x2//TIMEOUTx7
			"4012888888881881",	//TIMEOUTx8//1000
			"4222222222222"		//23
		);
//		$cards = array(
//			"4111111111111111",
//			"4012888888881881"
//		);
		foreach($cards as $key=>$value){
			$transaction['AMT'] = $amounts[0];//this should result in REJECTION. Ooo.
			$transaction['AMOUNT'] = $transaction['AMT'];
			//$transaction['ACCT'] = '4111111111111111';
			$transaction['ACCT'] = $value;

			$current_expected = '12';
			$response = $this->processor->inject_payflow_test_transaction($transaction);
			echo $transaction['ACCT'] . " gave us " . $response['RESULT'] . ":\n" . print_r($response, true);
		}
		$this->assertTrue($response['RESULT'] == $current_expected, "PayflowPro responded abnormally. (Should be $current_expected)" . print_r($response, true));

		$transaction_id = $response['PNREF'];
		$this->logCheckArrayAdd("Transaction ID: $transaction_id", "Transaction ID not logged.", $checkLogItems);

		$decoded = $this->processor->getPayflowProStatus($transaction_id, $creds);
		$this->assertTrue($decoded == $current_expected, "PayflowPro responded with the wrong status (should be '$current_expected')");
		$this->logCheckArrayAdd("PayflowPro result code: $decoded", "PayflowPro decoded result not logged: $decoded", $checkLogItems);
		$this->assertLogEntries($checkLogItems, true);
		$this->logCheckArrayRemove("Transaction ID: $transaction_id", $checkLogItems);
		$this->logCheckArrayRemove("PayflowPro result code: $decoded", $checkLogItems);
*/
	}

	/**
	 * bugs and re-bugs pfp if it returns a code 1000, which basically means
	 * ::shrug:: on their end.
	 * This just seems to happen periodically.
	 *
	 * @param $transaction the transaction we want to sent to pfp
	 * @param $tries Integer the number of times to re-try.
	 * @return string response from inject_payflow_test_transaction.
	 */
	public function persistent_add_pfp_transaction($transaction, $tries){
		$i = 0;
		$response = $this->processor->inject_payflow_test_transaction($transaction);
		while($i < $tries && ($response['RESULT'] == 1000)){
			echo "PFP result " . $response['RESULT'] . ". Retrying.";
			$response = $this->processor->inject_payflow_test_transaction($transaction);
			++$i;
		}
		return $response;
	}
 

	/**
	 * Tests the logs that get generated when we execute() with 10 objects in
	 * the pending queue, and set the test to fake a pfp response of '126' for
	 * all those items.
	 *
	 * @return null
	 */
	public function testExecuteLogs_allpending() {
		global $logdata;
		//$this->fail("Just don't.");
		//Clear out whatever queue you want to use for this, populate it, and then manpiulate.
		$this->assertEmptyQueueWithAck($this->processor->activemq_pending_queue);
		//right now, I'm adding 10 messages because it's less than the batch size. We need to also make a test that
		//overruns the batch size, and test for... something. Hm. Maybe that it handles all the records
		//with two successive calls to execute().
		$id_fields = array(
			0=> 'contribution_tracking_id',
			1=> 'gateway_txn_id'
		);
		$logdata = array();
		$items = 10;
		$added_ids = $this->assertAddTestMessagesToQueue($items, $this->processor->activemq_pending_queue, $id_fields, "pnd_", false);
		$this->assertItemAddLogs($items, true);

		$this->processor->setPFPTestMode('126');
		$this->processor->execute();

		//check aaaaaall the logs.
		$this->assertItemHandlingLogs($added_ids);

		//Check the contents of the destination queue to make sure all our stuff is there.
		$this->assertQueueContainsAllMessages($this->processor->activemq_pending_queue, $added_ids, 'contribution_tracking_id');
	}


	/**
	 * Tests the logs that get generated when we execute() with 10 objects in
	 * the pending queue, and set the test to fake a pfp response of '0' for
	 * all those items.
	 *
	 * @return null
	 */
	public function testExecuteLogs_allFinal() {
		global $logdata;
		//Clear out whatever queue you want to use for this, populate it, and then manpiulate.
		$this->assertEmptyQueueWithAck($this->processor->activemq_pending_queue);
		//right now, I'm adding 10 messages because it's less than the batch size. We need to also make a test that
		//overruns the batch size, and test for... something. Hm. Maybe that it handles all the records
		//with two successive calls to execute().
		$id_fields = array(
			0=> 'contribution_tracking_id',
			1=> 'gateway_txn_id'
		);
		$logdata = array();
		$items = 10;
		$added_ids = $this->assertAddTestMessagesToQueue($items, $this->processor->activemq_pending_queue, $id_fields, "fin_", false);
		$this->assertItemAddLogs($items, true);

		$this->processor->setPFPTestMode('0');
		$this->processor->execute();

		//check aaaaaall the logs.
		$this->assertItemHandlingLogs($added_ids);

		//Check the contents of the destination queue to make sure all our stuff is there.
		$this->assertQueueContainsAllMessages($this->processor->activemq_confirmed_queue, $added_ids, 'contribution_tracking_id');
	}

	/**
	 * asserts that the logs reflect that a certain number of items have been
	 * added to the queue.
	 *
	 * @param $count Int Number of items that should have add statements in the
	 * log.
	 * @return bool
	 */
	public function assertItemAddLogs($count, $clear = false){
		global $logdata;
		$msg1 = "Attempting to queue message...";
		$msg2 = "Result of queuing message: 1";

		$msg1_found = 0;
		$msg2_found = 0;
		foreach ($logdata as $whocares => $line) {
			if (strpos($line, $msg1) > 0) {
				++$msg1_found;
			}
			if (strpos($line, $msg2) > 0) {
				++$msg2_found;
			}
		}
		$assert = false;
		if ($msg1_found == $count && $msg2_found == $count){
			$assert = true;
		}
		if ($clear) {
			//clear the log
			$logdata = array();
		}
		$this->assertTrue($assert, "Not all items were found in the logs");
	}

	/**
	 * asserts that the ids have log entries that reflect a number of things.
	 * *Pending transaction logs, stomp frame logs, result handling logs, and
	 * some attempt to either connect to (or circumvent, in the interest of
	 * being able to test results we can't get pfp's test servers to generate
	 * consistently) PayflowPro. If any item is missing, the calling test fails.
	 *
	 * @param $added_ids Array An array of message IDs that are currently set up
	 * to be present in the pending queue.
	 * @return null
	 */
	public function assertItemHandlingLogs($added_ids) {
		global $logdata;
		//we should maybe kill the lines out of the log when we're done with them.
		//the two arrays below go $transaction => $log_line_number
		$pendingLines = $this->assertPendingTransactionLogs($added_ids);
		$stompLines = $this->assertStompFrameLogs($added_ids);
		$resultLines = $this->assertResultHandlingLogs($added_ids);

		//PFP Logs will be between the Pending and Result lines! Ha!
        foreach ($added_ids as $key=>$item_id){
			$found = "";
			$connected = "";
			$status = "";
			for ($i = $pendingLines[$item_id]; $i<$resultLines[$item_id]; ++$i){
				if (!$found){
					if (strpos($logdata[$i], "No PFP Credentials") > 0){
						$found = "no creds";
					}
					if (strpos($logdata[$i], "PayflowPro query array:") > 0){
						$found = "query array";
					}
				} elseif($found == "query_array") {
					if (strpos($logdata[$i], "Succesfully connected to PayflowPro") > 0){
						$connected = "success";
					}
					if (strpos($logdata[$i], "PayflowPro reported status:") > 0){
						$status = "reported";
					}
				}
			}
			$this->assertFalse(empty($found), "No PayflowPro Connection Information Logged for $item_id");
			if ($found == "query array"){
				$this->assertFalse(empty($connected), "PayflowPro Successful Connection Not Logged for $item_id");
				$this->assertFalse(empty($reported), "No PayflowPro Status Reported for $item_id");
			}
			//echo "Found pfp connection logs for $item_id"; //just checking.
        }
	}

	/**
	 * asserts that the transactions have log entries that reflect having been
	 * pulled from the pending queue.
	 * This also checks that the array version of the message was logged.
	 * If any item is missing, the calling test fails.
	 *
	 * @param $transactions Array an array of transaction ids that should have
	 * log entries.
	 * @return Array An array of message IDs/Log line numbers that
	 * contain the array formatted message.
	 */
	public function assertPendingTransactionLogs($transactions) {
		global $logdata;
		$ret = array();
		$pendingLines = $this->getLogLines("Pending transaction:");
		$transactions = $this->mapTransactionsToLogLines($transactions, $pendingLines, "[contribution_tracking_id] => ");

		//at this point, they are only still the same if a line was not found.
		foreach ($transactions as $id => $logline) {
			$this->assertTrue($id != $logline, "Transaction $id has no Pending Transaction line in the log");
		}

		return $transactions;
	}

	/**
	 * asserts that the transactions have log entries that reflect the entire
	 * raw stomp frame.
	 * If any item is missing, the calling test fails.
	 *
	 * @param $transactions Array an array of transaction ids that should have
	 * log entries.
	 * @return Array An array of message IDs/Log line numbers that
	 * contain the entire stomp frame for the message with that ID.
	 */
	public function assertStompFrameLogs($transactions) {
		global $logdata;
		$ret = array();
		$stompLines = $this->getLogLines("Stomp_Frame Object");
		$transactions = $this->mapTransactionsToLogLines($transactions, $stompLines, '"contribution_tracking_id":"');

		echo print_r($stompLines, true);
		echo print_r($transactions, true);


		//at this point, they are only still the same if a line was not found.
		foreach ($transactions as $id => $logline) {
			$this->assertTrue($id != $logline, "Transaction $id has no Stomp Frame in the log" . print_r($logdata, true));
		}

		return $transactions;
	}

	/**
	 * asserts that the transactions have log entries that reflect the full
	 * handling of the item, based on the status check result from Payflow Pro.
	 * If any item is missing, the calling test fails.
	 *
	 * @param $transactions Array an array of transaction ids that should have
	 * log entries.
	 * @return Array An array of message IDs/Log line numbers that
	 * contain the message id and the result code.
	 */
	public function assertResultHandlingLogs($transactions) {
		global $logdata;
		$ret = array();
		$resultLines = $this->getLogLines("Handling transaction ");
		$transactions = $this->mapTransactionsToLogLines($transactions, $resultLines);

		$forced_code = false;
		if (isset($this->processor->test_code)){
			$forced_code = $this->processor->test_code;
		}

		//at this point, they are only still the same if a line was not found.
		foreach ($transactions as $id => $logline) {
			$this->assertTrue($id != $logline, "Transaction $id has no Result Handling in the log");
			//BUT WAIT! There's more this time...

			if($forced_code){
				$this->assertContains("with code $forced_code", $logdata[$logline], "Forced Code($forced_code) not applied to item");
				switch($forced_code){
					case"0":
						$this->assertContains("Attempting to push message to confirmed queue:", $logdata[$logline+1],"Confirmed Message Attempt Not Logged");
						$this->assertContains("Succesfully pushed message to confirmed queue.", $logdata[$logline+4],"Confirmed Message Success Not Logged");
						break;
					case"126":
					case"26":
						$this->assertContains("Attempting to push message back to pending queue: ", $logdata[$logline+1],"Pending Message Pushed Back to Pending Attempt Not Logged");
						$this->assertContains("Succesfully pushed message back to pending queue", $logdata[$logline+4], "Message Pushed Back to Pending Not Logged.");
						break;
					default:
						$this->fail("Test Fail: You need to handle your forced code $forced_code");
				}
			} else { //no forced code: Could be mixed live data.
				//gosh, I wish I could switch on... not the entire next line.
				if(strpos($logdata[$logline+1], "Attempting to push message to confirmed queue:") > 0){
					$this->assertContains("with code 0", $logdata[$logline], "Message Confirmed WITHOUT Code 0");
					$this->assertContains("Succesfully pushed message to confirmed queue.", $logdata[$logline+4],"Confirmed Message Not Logged");
				} elseif(strpos($logdata[$logline+1], "Attempting to push message back to pending queue: ") > 0){
					$this->assertTrue((strpos($logdata[$logline], "with code 26") > 0 || strpos($logdata[$logline], "with code 126") > 0), "Message Pushed Back to Pending WITHOUT Code 26 or 126");
					$this->assertContains("Succesfully pushed message back to pending queue", $logdata[$logline+4], "Message Pushed Back to Pending Not Logged.");
				} else {
					$this->fail("Message is being ignored. This is almost certainly Not Good.");
				}
			}
		}

		return $transactions;
	}

	/**
	 * Checks the log for a particular line.
	 *
	 * @param $search String The line in the long we're looking for.
	 * @param $clear boolean If true, the logs will be wiped after the check has
	 * completed. If false, the log will not be altered.
	 * @return boolean True if $search was present in the log, else false
	 */
	public function checkLog($search, $clear = false) {
		//this should return something nice if it's got what you're looking for.
		global $logdata;
		$found = false;
		foreach ($logdata as $whocares => $line) {
			if (strpos($line, $search) > 0) {
				$found = true;
			}
		}
		if ($clear) {
			//clear the log
			$logdata = array();
		}
		return $found;
	}

	/**
	 * Asserts that all items in the $logCheckArray are present in the log.
	 *
	 * @param $logCheckArray Array An array of:
	 *		Search Strings => Error messages
	 * The error messages will be displayed on an assertion fail.
	 * @param $clear boolean If true, the logs will be wiped after the check has
	 * completed. If false, the log will not be altered.
	 * @return null
	 */
	public function assertLogEntries($logCheckArray, $clear = false) {
		global $logdata;
		foreach ($logCheckArray as $whocares => $set) {
			$this->assertTrue($this->checkLog($set['search']), $set['display_error']);
		}
		if ($clear) {
			//clear the log
			$logdata = array();
		}
	}

	/**
	 * Adds a log check line to an array, by reference. Used in staging the
	 * $logCheckArray parameter for assertAllLogEntries.
	 *
	 * @param $search String The line we want to locate in the log
	 * @param $display_error String the error we want to display when we don't
	 * find $search in the log.
	 * The error messages will be displayed on an assertion fail.
	 * @param $array Array The array we're adding the parameters to.
	 * @return null ($array is altered by reference)
	 */
	public function logCheckArrayAdd($search, $display_error, &$array) {
		$array[] = array(
			'search' => $search,
			'display_error' => $display_error
		);
	}

	/**
	 * Removes a log check line from an array, by reference. Used in staging the
	 * $logCheckArray parameter for assertAllLogEntries.
	 *
	 * @param $search String The search string we no longer want to check for,
	 * in the log.
	 * @param $array Array The array we're removing the parameters from.
	 * @return null ($array is altered by reference)
	 */
	public function logCheckArrayRemove($search, &$array) {
		foreach ($array as $whocares => $set) {
			if ($set['search'] == $search) {
				unset($array[$whocares]);
			}
		}
	}

	/**
	 * Pulls lines from the log that match a search parameter
	 *
	 * @param $match String The string we are scanning the log for.
	 * @return array An array of [$line_no]=>$line_data for all log line matches
	 */
	public function getLogLines($match){
		global $logdata;
		$resultLines = array();
		foreach ($logdata as $line_no => $line_data) {
			if (strpos($line_data, $match) > 0) {
				$resultLines[$line_no] = $line_data;
			}
		}
		return $resultLines;
	}

	/**
	 * Maps an array of transaction IDs to the log lines that contain them.
	 * Assumes that the $loglines array was already pared down by some
	 * parameter so both parameters would be roughly 1:1. If they are not, it
	 * would just return the --last-- instance of the transaction in the log
	 * (and be wildly inefficient).
	 *
	 * @param $transactions Array An array of transactions we are looking for.
	 * @param $loglines Array An array of loglines which probably contain
	 * mentions of $transactions
	 * @return array An array of $transaction=>$line_no, or
	 * $transaction=>$transaction if no match was found.
	 */
	public function mapTransactionsToLogLines($transactions, $loglines, $prefix = ''){
		foreach ($transactions as $id => $transaction) {
			foreach ($loglines as $line_no => $line_data) {
				if (strpos($line_data, $prefix . $id) > 0) {
					$transactions[$id] = $line_no;
				}
				break;
			}
			if (array_key_exists($transactions[$id], $loglines)) { //gofaster.
				unset($loglines[$transactions[$id]]);
			}
		}
		return $transactions;
	}
}
?>
