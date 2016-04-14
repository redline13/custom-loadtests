<?php

require('vendor/autoload.php');
require_once('LoadTestingTest.class.php');

// Include WebSocket Client
use WebSocket\Client;

/** WebSocket Load Testing */
class CustomTest extends LoadTestingTest
{
  /**
   * Constructor
   * @param int $testNum Test number
   * @param string $rand Random token for test
   */
  public function __construct($testNum, $rand)
  {
    // Call parent constructor
    parent::__construct($testNum, $rand);
  }
  
  /**
   * Start the test
   * @throws LoadTestingTestException
   */
  public function startTest()
  {
  	// Capture start time and userid.
	$startTime = time();
	$userId = 'user-'.$this->testNum;

    	try {
      		// Load page
      		$this->loadPage();
		
		// Capture overal time for running test plan	
		$endTime = time();
		$totalTime = $endTime - $startTime;
		recordPageTime($endTime, $totalTime, true, 0);
			
    	} catch (Exception $e) {

		// Capture time and error if plan failed.
		$endTime = time();
		$totalTime = $endTime - $startTime;
		recordPageTime($endTime, $totalTime, false, 0);
			      
      		// Throw exception
      		throw $e;
    	}
  }
  
  /**
   * Send and receive websocket calls.
   */
  public function loadPage()
  {
  	// Create connection
	$client = null;
	try{
		$client = new Client("ws://echo.websocket.org/");
	} catch( Exception $e ){
		recordError("Failed to create connection." . $e->getMessage() );
		return;
	}
	
	// Small Iteration for 25 requests 
	for ($i = 0; $i < 25 ; $i++) { 
		// Capture start  and captuer success/fail for 'Sending'
		$startTime = time();
		try{
			$client->send("Hello WebSocket.org!");
			$this->sendResult("Send Hello", $startTime);
		} catch(Exception $e){
			$this->sendResult( "Send Hello", $startTime, 0, $e->getMessage());
			continue;
		}

		// Capture start  and captuer success/fail for 'Receiving'
		$startTime = time();
		try{
			$msg = $client->receive(); // Will output 'Hello WebSocket.org!'
			$this->sendResult("Receive", $startTime, strlen($msg) );
		} catch(Exception $e){
			$this->sendResult( "Receive", $startTime, 0, $e->getMessage());
			continue;
		}
	}
  }
	
	/**
	 * Wrap sending some results and errors for requests
	 */
	protected function sendResult( $key, $startTime, $kb = 0 , $error = null ){
		$fail = !empty($error);
		$endTime = time();
		$totalTime = $endTime - $startTime;
		recordURLPageLoad( $key, $endTime, $totalTime, $fail, $kb);
		if ( $fail ){
			recordError("$key Error: $error");
		}
	}
}
