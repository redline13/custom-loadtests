<?php

require_once('LoadTestingTest.class.php');

define( 'SITE_USERNAME', "EMAIL_ADDRESS" );
define( 'SITE_PASSWORD', "PASSWORD" );

/**
 * Example testing a login page on dictionary.com.
 * To play with this test create an account on dictionary.com and
 * use your username+password in the definition above. The parent
 * class documentation @see https://www.redline13.com/customTestDoc/class-LoadTestingTest.html
 */
class CustomTest extends LoadTestingTest
{
	/**
	 * Constructor needs to pass up the informationn,
	 *
	 * @param int $testNum Test number, if you specify simulate 40 users - this represents that user.
	 * @param string $rand The rand is a token used to keep track of this test, not used, just pass to parent.
	 */
	public function __construct($testNum, $rand)
	{
		parent::__construct($testNum, $rand);
	}
	
	/**
	 * Start test is the main entry point.
	 * Here we script/control the flow of the test.
	 *
	 * Typically in a custom test we consider the start and stop of this function as the
	 * 'Page' time, really the overall time for the set of operations performed by the user.
	 *
	 * Within that page we have URLs, really the components that make up our overall ('page') time. This
	 * could be actual URLs, Code Blocks, Calls to DB, ....
	 *
	 * Page = 520ms
	 *   URL1 = 200ms
	 *   URL2 = 200ms
	 *   SOME CODE (recorded as URL3) = 100ms
	 * The missing time might be spent in some in-between parsing operations.
	 *
	 * @throws LoadTestingTestException
	 */
	public function startTest()
	{
		// Track start time to record the overall
		$startTime = time();
		// Track the amount of delay.
		$delay = 0;
		// Track overall bytes captured.
		$bytes = 0;
		try {

			// Echo statements output will be shown in our logs.out for the test.
			// This output is avialalbe in a paid subscrpition.
			echo "Starting test for user {$this->testNum}.\n";

			// Set delay that will be used with each call to goToUrl
			// set-Delay() @see https://www.redline13.com/customTestDoc/class-LoadTestingTest.html#_setDelay
			$this->setDelay(1000, 2000);	// 1 - 30 seconds

			// Load page
			$page = $this->loadLoginPage();
			// How many bytes.
			$bytes += strlen(serialize($page));
			// We need track the delay used since we don't want this in our performance calcuation.
			// getDelay() @see https://www.redline13.com/customTestDoc/class-LoadTestingTest.html#_getDelay
			$delay += ($this->getDelay()/1000);

			// Do Login
			$page = $this->doLogin($page);
			// How many bytes.
			$bytes += strlen(serialize($page));
			// We need track the delay used since we don't want this in our performance calcuation.
			$delay += ($this->getDelay()/1000);

			// Do Search
			$page = $this->doSearch($page);
			// How many bytes.
			$bytes += strlen(serialize($page));
			// We need track the delay used since we don't want this in our performance calcuation.
			$delay += ($this->getDelay()/1000);

			// Analyze Search Results.
			$this->analyzeSearchPage($page);
			// There is no delay or data retrieved, nothing to do for $bytes and $delay.
			
			// You can see how to generate an error, and see it in the error section. 
			// This will not throw an error, page will be recorded as normal.
			$this->generateFakeError();

			// The test really ended here, let's not capture the cleanup
			$endTime = time();

			// Clean up session file
			$this->session->cleanup();
			
		} catch (Exception $e) {

			// Echo statements output will be shown in our logs.out for the test.
			// This output is avialalbe in a paid subscrpition.
			echo "Test failed.\n";

			// We control the recording of the overall request.
			// $endTime - is the timestamp we wanat to record the event on
			// $totalTime time it took for the overall request
			// true - this field is isThisError, hence in this case it is an error.
			// $bytes - KB, the size of the response.
			// recordPageTime @see https://www.redline13.com/customTestDoc/function-recordPageTime.html
			$endTime = time();
			$totalTime = $endTime - $startTime - $delay;
			recordPageTime($endTime, $totalTime, true, $bytes/1000);
			
			// Throw exception
			throw $e;
		}

		// We control the recording of the overall request.
		// $endTime - is the timestamp we wanat to record the event on
		// $totalTime time it took for the overall request
		// false - this field is isThisError, hence in this case it is NOT an error, but success.
		// $bytes - KB, the size of the response.
		// recordPageTime @see https://www.redline13.com/customTestDoc/function-recordPageTime.html
		$totalTime = $endTime - $startTime - $delay;
		recordPageTime($endTime, $totalTime, false, $bytes/1000);
		echo "BYTES RECORDED $bytes.\n";
	}
	
	/**
	 * This will open the login page for dictionary.com and we will
	 * let the underlying call record the URL load time.  We use
	 * $this->session which is a reference to an objec that will keep track of cookiees and
	 * build a user session. partially simulating the behavior of a browser.
	 *
	 * $this->session is @see https://www.redline13.com/customTestDoc/class-LoadTestingSession.html
	 *
	 * @return LoadTestingPageResponse Page @see https://www.redline13.com/customTestDoc/class-LoadTestingPageResponse.html
	 * @throws LoadTestingTestException
	 */
	public function loadLoginPage()
	{
		// Go to Login Page
		// 	'http://app.dictionary.com/login' - URL to load
		// 	null - this represent a DATA array, if passed in the request will be a POST, otherwise a GET.
		// 	[] = custome headers, which we have none.
		// 	true - Save Data - this will write out the contents of the response and make it available in the output after test completion (pro-feature)
		//	false - Record as PAGE (true) or URL (false).  Since we are controlling PAGE in our startTest we set to false.
		$page = $this->session->goToUrl('http://app.dictionary.com/login', null, [], true, false );

		// The resopnse is a custom object @see https://www.redline13.com/customTestDoc/class-LoadTestingPageResponse.html
		// We can test to see if there is a form element named username, validating our request.
		if (!in_array('username', $page->getFormElemNames()))
		{
			// Throw exception.  This will end the test on error and record Exception message
			throw new LogicException('Missing expected for login form.');
		}

		// Returns @see https://www.redline13.com/customTestDoc/class-LoadTestingPageResponse.html
		return $page;
	}

	/**
	 * This will login the user by using a POST request to the login form.
	 * @return LoadTestingPageResponse Page @see https://www.redline13.com/customTestDoc/class-LoadTestingPageResponse.html
	 */
	public function doLogin()
	{
		// Execute a login
		// 	'http://app.dictionary.com/login' - URL to load
		// 	[ source, ... ] - this represent a DATA array, and will therefore execute a POST request on the URL.
		// 	[] = custome headers, which we have none.
		// 	true - Save Data - this will write out the contents of the response and make it available in the output after test completion (pro-feature)
		//	false - Record as PAGE (true) or URL (false).  Since we are controlling PAGE in our startTest we set to false.
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
	 * @return LoadTestingPageResponse Page @see https://www.redline13.com/customTestDoc/class-LoadTestingPageResponse.html
	 */
	public function doSearch()
	{
		// Execute a search.
		// 	'http://app.dictionary.com/login' - URL to load
		// 	null - this represent a DATA array, if passed in the request will be a POST, otherwise a GET.
		// 	[] = custome headers, which we have none.
		// 	true - Save Data - this will write out the contents of the response and make it available in the output after test completion (pro-feature)
		//	false - Record as PAGE (true) or URL (false).  Since we are controlling PAGE in our startTest we set to false.
		$page = $this->session->goToUrl("http://www.dictionary.com/browse/testing?s=t", null, [], true, false );
		return $page;
	}

	/**
	 * Echo some information about the request and analyze the data we know about.
	 * @param LoadTestingPageResponse $page @see https://www.redline13.com/customTestDoc/class-LoadTestingPageResponse.html
	 */
	public function analyzeSearchPage($page)
	{
		$startTime = time();

		$links = count($page->getLinks());
		echo "# of Links on page: $links\n";

		$elems = count($page->getFormElems());
		echo "# of Form Elements on page: $elems\n";

		$css = count($page->getCssHrefs());
		echo "# of CSS Resources: $css\n";

		$hrefs = count($page->getImageHrefs());
		echo "# of Images: $hrefs\n";

		$js = count($page->getJavascriptSrcs());
		echo "# of JS Files: $js\n";

		$endTime = time();
		$totalTime = $endTime - $startTime;
		// Record this block of time as a URL
		// AnalyzeSearchPage - name to show in reports
		// $endTime - timestamp
		// $totalTime - total time of this block
		// false - boolean true there was an error, false this was a success
		// 0 - Kilobytes of response data, not relevant for this one.
		// recordURLPageLoad - @see https://www.redline13.com/customTestDoc/function-recordURLPageLoad.html
		recordURLPageLoad( "AnalyzeSearchPage", $endTime, $totalTime, false, 0);
	}
	
	/**
	 * Generate an error in the URL section and see error show up in the error message log within page.
	 * @see https://www.redline13.com/customTestDoc/function-recordError.html
	 */
	public function generateFakeError()
	{
		$endTime = time();
		// Record this block as a URL but with Error set to true.
		// FakeError - name to show in reports
		// $endTime - timestamp
		// 0 - total time of this block 
		// true - boolean true there was an error, true since we are faking an error.
		// 0 - Kilobytes of response data, not relevant for this one.
		// recordURLPageLoad - @see https://www.redline13.com/customTestDoc/function-recordURLPageLoad.html
		recordURLPageLoad( "FakeError", $endTime, 0, true, 0 );
		
		// We can also record error messages using https://www.redline13.com/customTestDoc/function-recordError.html
		recordError( "We generated an a fake error" );
	}
}
