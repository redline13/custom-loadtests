<?php

require_once('LoadTestingTest.class.php');

define( 'SITE_USERNAME', "EMAIL_ADDRESS" );
define( 'SITE_PASSWORD', "PASSWORD" );

/** Single Page Load Testing */
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
		$startTime = time();
		try {
			// Set delay that will be used with each call to goToUrl
			$this->setDelay(1000, 2000);	// 1 - 30 seconds

			// Load page
			$page = $this->loadLoginPage();
			
			// Do search
			$page = $this->doLogin($page);
			
			// Do Login
			$page = $this->doSearch($page);
			
			// Clean up session file
			$this->session->cleanup();
			
		} catch (Exception $e) {
			echo "Test failed.\n";
			$endTime = time();
			$totalTime = $endTime - $startTime;
			recordPageTime($endTime, $totalTime, true, 0);
			
			// Throw exception
			throw $e;
		}

		$endTime = time();
		$totalTime = $endTime - $startTime;
		recordPageTime($endTime, $totalTime, false, 0);
	}
	
	/**
	 * Load page
	 * @return LoadTestingPageResponse Page
	 * @throws LoadTestingTestException
	 */
	public function loadLoginPage()
	{
		// Go to Login Page
		$page = $this->session->goToUrl('http://app.dictionary.com/login', null, [], true, false );
		// Simple check for error
		if (!in_array('username', $page->getFormElemNames()))
		{
			// Throw exception.  This will end the test on error and record Exception message
			throw new LogicException('Missing expected for login form.');
		}
		return $page;
	}

	public function doLogin()
	{
		$page = $this->session->goToUrl('http://app.dictionary.com/login/core/fullpage', [
				'source'=>'undefined',
				'logindest'=>'http://www.dictionary.com',
				'username'=>SITE_USERNAME,
				'password'=>SITE_PASSWORD,
				'keep_me_signed'=>1
			], 
			[],
			true,
			false
		);
		return $page;
	}
	/**
	 * Do search
	 * @param LoadTestingPageResponse $page Current page
	 * @return LoadTestingPageResponse Next page
	 */
	public function doSearch()
	{
		$page = $this->session->goToUrl("http://www.dictionary.com/browse/testing?s=t", null, [], true, false );
		return $page;
	}
}
