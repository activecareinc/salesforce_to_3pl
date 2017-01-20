<?php
/*
 * Class: PHPUnit_Extensions_Sauceium
 *
 * extends the PHPUnit_Extentions_Selenium2TestCase
 * and allows any BTP engineer to use regular
 * PHPUnit_Entenstions_Selenium2TestCase functionality
 * both on local selenium servers as well as on sauce selenium servers
 * when invoke, this requires --configuration option when running a test
 *
 * @see PHPUnit_Extensions_SeleniumTestCase
 */
class PHPUnit_Extensions_Sauceium extends PHPUnit_Extensions_SeleniumTestCase {
	// host to use later
	protected $host;
	protected $host_set = false;

	// host to use later
	protected $port;
	protected $port_set = false;

	// what environment?
	protected $environment;
	protected $environment_set = false;

	// what is our base_url for all tests?
	protected $base_url;
	protected $base_url_set = false;

	// what browser do we use?
	protected $browser;
	protected $browser_set = true;

	// carats http authentication
	protected $http_user;
	protected $http_pass;
	protected $http_base;
	protected $http_user_set = false;
	protected $http_pass_set = false;
	protected $http_base_set = false;

	// sauce specific logins
	protected $sauce_user = 'bridge1';
	protected $sauce_pass = 'ab6b37fc-23e7-46e3-9322-44a83fdc1504';
	protected $sauce_user_set = false;
	protected $sauce_pass_set = false;

	// code coverage
	protected $coverageScriptUrl;

	/*
	 * setUp
	 *
	 * Lets setup our browser and url
	 *
	 */
	protected function setUp() {
		// code coverage
		if(defined('COVERAGE_SCRIPT') === true) {
			$this->coverageScriptUrl = COVERAGE_SCRIPT;
		}

		// force selenium to share/notshare the session information
		$this->shareSession(true);

		// set the variables from the arguments passed
		$this->setEnvironment($_SERVER['argv']);

		// set default timeout to be 120 secs
		$this->setTimeout(60);

		// now use the host
		$this->setHost($this->host);

		// now use the port
		$this->setPort((int)$this->port);

		// set the browser to corresponding environment
		// for local and remote, it should only use the default browser config
		// while for sauuce it should passed json encoded with the sauce creds
		switch($this->environment) {
			case 'local':
			case 'remote':
				$this->setBrowser($this->browser);
				break;
			default:
				$this->setBrowser(
					json_encode(
						array(
							'username'			=> $this->sauce_user,
							'access-key'		=> $this->sauce_pass,
							'name'				=> get_called_class().'::'.$this->getName(),
							'browser'			=> $this->browser,
							'browser-version'	=> '20',
							'os'				=> 'Windows 2008',
							'screen-resolution'	=> '1280x1024'
						)
					)
				);
				break;
		}

		// set the base url for the browser
		$this->setBrowserUrl($this->base_url);

		// prepare the session
		$this->prepareTestSession();
	}

	/*
	 * tearDown
	 *
	 * This is the local override for the tearDown() function
	 */
	protected function tearDown() {
		// call the parent class tearDown()
		parent::tearDown();
	}

	/*
	 * setEnvironment
	 *
	 * This function checks all the values in $_SERVER['argv'] and saves the args
	 * that we need for later use
	 *
	 * @param array $argv
	 */
	protected function setEnvironment($argv = array()) {
		// what parameters should we accept?
		$args_valid = array(
			'environment',
			'http_user',
			'http_pass',
			'http_base',
			'sauce_user',
			'sauce_pass',
			'browser',
			'host',
			'port'
		);

		// what environments will we allow?
		$environments_valid = array(
			'release',
			'staging',
			'dev'
		);

		// lets set some defaults
		// default host is sauce because local and remote could have not been setup yet
		$this->environment	= VIEW_ENVIRONMENT;
		$this->host			= 'ondemand.saucelabs.com';
		$this->port			= 4444;
		$this->browser		= '*firefox';
		$this->http_user	= '';
		$this->http_pass	= '';
		$this->http_base	= VIEW_DOMAIN;

		// check if we have the args that we need
		if (is_array($argv) && count($argv)) {
			// check if we have valid args
			// to do this we need to loop all of them
			foreach($argv as $arg) {
				// get the args key and value
				$arg = explode('=', $arg);

				// now we clean the key
				$arg[0] = str_replace('--', '', $arg[0]);

				// check if we have a match against the valid args array
				if (in_array($arg[0], $args_valid)) {
					// Get the value
					$arg[1] = trim($arg[1]);

					// Check if we have a valid value
					if (strlen($arg[1])) {
						// check if we are setting an environment
						if ($arg[0] == 'environment') {
							// validate the environment value is valid
							if (in_array($arg[1], $environments_valid)) {
								// Now we use this value
								$this->$arg[0] = $arg[1];
							}
						} else {
							// we are not setting environment so we can use the value
							$this->$arg[0] = $arg[1];
						}

						// what is the set_name?
						$arg[0] = "{$arg[0]}_set";

						// set as set? :D
						$this->$arg[0] = true;
					}
				}
			}
		}

		// check if we want to use the sauce server
		// or the remote server or just local
		// check if host was not overridden
		if (!$this->host_set) {
			switch($this->environment) {
				case 'local':
					$this->host = 'btp-workstation';
					$this->port = 4444;
					break;
				case 'remote':
					$this->host = 'btp-s.bridgetechnologypartners.com';
					$this->port = 4444;
					break;
				// sauce is default
				default:
					$this->host = 'ondemand.saucelabs.com';
					$this->port = 80;
					break;
			}
		}

		// build the default base url
		$this->base_url		= "http://{$this->http_base}/";

		// build the base_url with http_auth
		if (strlen($this->http_user) && strlen($this->http_pass)) {
			$this->base_url		= "http://{$this->http_user}:{$this->http_pass}@{$this->http_base}/";
		}
	}
	/**
	 * overrides the waitForPageToLoad to assert php error	s
	 * such as fatal, notice and warnings
	 * @param int $timeout
	 * @return void
	 */
	 public function waitForPageToLoad($timeout = '') {
	  	if(strlen(trim($timeout)) < 1) {
		 	$timeout = 30000;
	  	}

		 parent::waitForPageToLoad($timeout);

		// // assuming codeigniter framework
		 $this->assertElementNotPresent('css=h4:contains("A PHP Error was encountered")');

		// assuming xdebug is installed

		// standard php error messages

		}
}
