<?php

require_once('LoadTestingTest.class.php');

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
		try {
			// Load page
			$page = $this->loadPage();
			
			// Clean up session file
			$this->session->cleanup();
			
		} catch (Exception $e) {
			echo "Test failed.\n";
			
			// Throw exception
			throw $e;
		}
	}
	
	/**
	 * Load page
	 * @return LoadTestingPageResponse Page
	 * @throws LoadTestingTestException
	 */
	public function loadPage()
	{
		// Sleep between 0.000001 and 10 seconds
		usleep(rand(1, 10000000));
		
		// Load page
		$page = $this->session->goToUrl('http://example.com');
		
		return $page;
	}
}
