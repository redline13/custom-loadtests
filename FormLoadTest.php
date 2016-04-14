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
			
			// Set delay that will be used with each call to goToUrl
			$this->setDelay(5000, 30000);	// 5 - 30 seconds
			
			// Do search
			$page = $this->doQuery($page);
			
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
		// Set delay that will be used with each call to goToUrl
		$this->setDelay(1000, 30000);	// 1 - 30 seconds
		
		// Go to first page
		$page = $this->session->goToUrl('http://www.google.com');
		
		// Simple check for error
		if (!in_array('q', $page->getFormElemNames()))
		{
			// Throw exception.  This will end the test on error and record Exception message
			throw new LogicException('Missing expected for parameter.');
		}
		
		return $page;
	}
	
	/**
	 * Do search
	 * @param LoadTestingPageResponse $page Current page
	 * @return LoadTestingPageResponse Next page
	 */
	public function doQuery(LoadTestingPageResponse $page)
	{
		// Build post
		$get = array();
		$formUrl = null;
		foreach ($page->getFormElems() as $elem)
		{
			$name = $elem->getAttribute('name');
			if (!empty($name))
			{
				if ($name == 'q')
				{
					$get[$name] = 'RedLine13 Load Testing';
					
					// Get form URL
					$elem2 = $elem->parentNode;
					while ($elem2 != null)
					{
						if (isset($elem2->tagName) && strtolower($elem2->tagName) == 'form')
						{
							$formUrl = $elem2->getAttribute('action');
							if (isset($formUrl[0]) && $formUrl[0] = '/')
								$formUrl = 'http://www.google.com' . $formUrl;
							break;
						}
						$elem2 = $elem2->parentNode;
					}
				}
			}
		}
		
		$url = $formUrl . '?' . http_build_query($get);
		$page = $this->session->goToUrl($url);
		
		return $page;
	}
}
