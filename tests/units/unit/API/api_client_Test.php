<?php

// require library
require_once (__DIR__ . '/../../../../php_includes/application/libraries/API_Client_Transport.php');
require_once (__DIR__ . '/../../../../php_includes/application/libraries/API_Client.php');
require_once (__DIR__ . '/../../../system/config.php');

/**
 * API_Client_Test
 */
class API_Client_Test extends PHPUnit_Framework_TestCase {

	/**
	 * Transport class
	 *
	 * @var API_Client_Transport
	 */
	protected static $TRANSPORT;

	/**
	 * Session class
	 *
	 * @var CI_Session
	 */
	protected static $SESSION;

	/**
	 *
	 * @method setUpBeforeClass
	 * This method runs first before the first test of the test case class,
	 * Setup class libraries. Creates account for login credentials
	 *
	 * @return void
	 */
	public static function setUpBeforeClass() {
	}

	/**
	 *
	 * @method tearDownAfterClass
	 * This method runs after the last test of the test case class,
	 * Deletes all data created during the test
	 *
	 * @return void
	 */
	public static function tearDownAfterClass() {
	}

	/**
	 *
	 * @method setUp
	 * Run once before each test case
	 *
	 * @see PHPUnit_Framework_TestCase::setUp()
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		// Initialize transport api client
		self::$TRANSPORT = $this->getMock('API_Client_Transport');
		self::$SESSION = $this->getMockBuilder('Session')->setMethods(array(
			'set_userdata'
		))->getMock();

		// Configure the session.
		self::$SESSION->method('set_userdata')->willReturn('foo');
	}

	/**
	 *
	 * @method tearDown
	 * Run once after each test case
	 *
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 *
	 * @method test_generate_state
	 * @return void
	 */
	public function test_generate_state() {
		$client = new API_Client();
		$state = $client->generate_state();

		$this->assertEquals(strlen($state), 18);
	}

	/**
	 * verify generation of hash
	 *
	 * @return void
	 */
	public function test_generate_hash() {
		$client = new API_Client();
		$hash = $client->generate_hash();

		// verify the length is 48 characters long
		// since it is expecting to generate 24bytes of random pseudo characters
		$this->assertEquals(strlen($hash), 48);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $email, must be a valid string.
	 */
	public function test_authenticate_null_email() {
		$client = new API_Client();
		$client->authenticate(null, null);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $password, must be a valid string.
	 */
	public function test_authenticate_null_password() {
		$client = new API_Client();
		$client->authenticate('engineering_test_100@bridgetechnologypartners.com', null);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $email, must be a valid email.
	 */
	public function test_authenticate_invalid_email() {
		$client = new API_Client();
		$client->authenticate('foo', 'bar');
	}

	/**
	 *
	 * @method test_authenticate
	 * @return void
	 */
	public function test_authenticate() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();
		$result->data = new stdClass();
		$result->status = 200;
		$result->data->access_token = 'a';
		$result->data->refresh_token = 'a';
		$result->data->expire_on = time() + 60;

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);

		// Authenticate
		$result = $client->authenticate('GMYBM154_131@bridgetechnologypartners.com', 'CXHMfwDJt9ra');
		$this->assertEquals($result->status, 200);
	}

	/**
	 *
	 * @method test_authorize_invalid_code
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $auth_code, must be a valid string.
	 */
	public function test_authorize_invalid_code() {
		$client = new API_Client();
		$client->authorize(null, null);
	}

	/**
	 *
	 * @method test_authorize_invalid_code
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $state, must be a valid string.
	 */
	public function test_authorize_invalid_state() {
		$client = new API_Client();
		$client->authorize(uniqid(), null);
	}

	/**
	 *
	 * @method test_authorize_invalid_returned_access_token
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Invalid access token returned.
	 */
	public function test_authorize_invalid_returned_access_token() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);
		$client->authorize(uniqid(), uniqid());
	}

	/**
	 *
	 * @method test_authorize_invalid_returned_refresh_token
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Invalid refresh token returned.
	 */
	public function test_authorize_invalid_returned_refresh_token() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();
		$result->access_token = uniqid();

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);
		$client->authorize(uniqid(), uniqid());
	}

	/**
	 *
	 * @method test_authorize_invalid_returned_expires_on
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Invalid refresh token, already expired.
	 */
	public function test_authorize_invalid_returned_expires_on() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();
		$result->access_token = uniqid();
		$result->refresh_token = uniqid();
		$result->expires_on = strtotime("-1 hour");

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);
		$client->authorize(uniqid(), uniqid());
	}

	/**
	 *
	 * @method test_authorize
	 * @return void
	 */
	public function test_authorize() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();
		$result->access_token = uniqid();
		$result->refresh_token = uniqid();
		$result->expires_on = strtotime("+1 hour");

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Mock session
		self::$SESSION = $this->getMockBuilder('CI_Session')->setMethods(array(
				'set_userdata'
		))->getMock();

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);
		$client->authorize(uniqid(), uniqid());
	}

	/**
	 *
	 * @method test_refres_token_invalid_token
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $refresh_token, must be a valid string.
	 */
	public function test_refres_token_invalid_token() {
		$client = new API_Client();
		$client->refresh_token(null);
	}

	/**
	 *
	 * @method test_refresh_token_invalid_returned_access_token
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Invalid access token returned.
	 */
	public function test_refresh_token_invalid_returned_access_token() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);
		$client->refresh_token(uniqid());
	}

	/**
	 *
	 * @method test_refresh_token_invalid_returned_refresh_token
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Invalid refresh token returned.
	 */
	public function test_refresh_token_invalid_returned_refresh_token() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();
		$result->access_token = uniqid();

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);
		$client->refresh_token(uniqid());
	}

	/**
	 *
	 * @method test_refresh_token_invalid_returned_expires_on
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Invalid refresh token, already expired.
	 */
	public function test_refresh_token_invalid_returned_expires_on() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();
		$result->access_token = uniqid();
		$result->refresh_token = uniqid();
		$result->expires_on = strtotime("-1 hour");

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);
		$client->refresh_token(uniqid());
	}

	/**
	 *
	 * @method test_refresh_token
	 * @return void
	 */
	public function test_refresh_token() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();
		$result->access_token = uniqid();
		$result->refresh_token = uniqid();
		$result->expires_on = strtotime("+1 hour");

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Mock session
		self::$SESSION = $this->getMockBuilder('CI_Session')->setMethods(array(
				'set_userdata'
		))->getMock();

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);

		$client->refresh_token(uniqid());
	}

	/**
	 *
	 * @method test_request_null_endpoint
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $endpoint, must be a valid string.
	 */
	public function test_request_null_endpoint() {
		$client = new API_Client();
		$client->request(null, null, null);
	}

	/**
	 *
	 * @method test_request_null_data
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $data, must be an array with values.
	 */
	public function test_request_null_data() {
		$client = new API_Client();
		$client->request('http:127.0.0.1', null, new stdClass());
	}

	/**
	 *
	 * @method test_request_empty_array_data
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $data, must be an array with values.
	 */
	public function test_request_empty_array_data() {
		$client = new API_Client();
		$client->request('http:127.0.0.1', array(), new stdClass());
	}

	/**
	 *
	 * @method test_request_empty_array_data
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $token, must be a valid token object.
	 */
	public function test_request_non_object_token() {
		$client = new API_Client();
		$client->request('http:127.0.0.1', array(
			'foo' => 'bar'
		), 'asdf');
	}

	/**
	 *
	 * @method test_request_error_result
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage There was an error getting the result.
	 */
	public function test_request_error_result() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();
		$result->error = uniqid();
		$result->status = 404;

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Mock session
		self::$SESSION = $this->getMockBuilder('CI_Session')->setMethods(array(
			'set_userdata',
			'userdata'
		))->getMock();

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);

		// Token
		$token = new stdClass();
		$token->data = new stdClass();
		$token->data->access_token = uniqid();
		$token->data->refresh_token = uniqid();

		$client->request('http://127.0.0.1', array(
			'foo' => 'bar'
		), $token);
	}

	/**
	 *
	 * @method test_request
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Invalid access token returned.
	 */
	public function test_request_expired_token() {
		$client = new API_Client();

		$bytes = openssl_random_pseudo_bytes(9, $cstrong);
		$access_token = bin2hex($bytes);

		// Result value
		$result = new stdClass();
		$result->data = new stdClass();
		$result->data->refresh_token = uniqid();
		$result->data->expires_on = strtotime("+1 hour");
		$result->status = API_STATUS_CODE_REFRESH_TOKEN;

		// Token
		$token = new stdClass();
		$token->data = new stdClass();
		$token->data->access_token = uniqid();
		$token->data->refresh_token = uniqid();

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Mock session
		self::$SESSION = $this->getMockBuilder('CI_Session')->setMethods(array(
			'set_userdata',
			'userdata'
		))->getMock();

		self::$SESSION->expects($this->any())->method('userdata')->will($this->returnValue($token));

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);

		$client->request('http://127.0.0.1', array(
				'foo' => 'bar'
		), $token);
	}

	/**
	 *
	 * @method test_request
	 * @return void
	 */
	public function test_request() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();
		$result->data = new stdClass();
		$result->data->access_token = uniqid();
		$result->data->refresh_token = uniqid();
		$result->data->expires_on = strtotime("+1 hour");
		$result->status = 200;

		// Token
		$token = new stdClass();
		$token->data = new stdClass();
		$token->data->access_token = uniqid();
		$token->data->refresh_token = uniqid();

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Mock session
		self::$SESSION = $this->getMockBuilder('CI_Session')->setMethods(array(
				'set_userdata',
				'userdata'
		))->getMock();

		self::$SESSION->expects($this->any())->method('userdata')->will($this->returnValue($token));

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);

		$result = $client->request('http://127.0.0.1', array(
				'foo' => 'bar'
		), $token);
		$this->assertTrue(is_a($result, 'stdClass'));
	}

	/**
	 *
	 * @method test_request_without_token
	 * @depends test_request
	 * @return void
	 */
	public function test_request_without_token() {
		$client = new API_Client();

		// Result value
		$result = new stdClass();
		$result->data = new stdClass();
		$result->data->access_token = uniqid();
		$result->data->refresh_token = uniqid();
		$result->expires_on = strtotime("+1 hour");
		$result->status = 200;

		// Token
		$token = new stdClass();
		$token->access_token = uniqid();
		$token->refresh_token = uniqid();

		// Mock transport
		self::$TRANSPORT->expects($this->any())->method('send')->will($this->returnValue($result));

		// Mock session
		self::$SESSION = $this->getMockBuilder('CI_Session')->setMethods(array(
			'set_userdata',
			'userdata'
		))->getMock();

		self::$SESSION->expects($this->any())->method('userdata')->will($this->returnValue($token));

		// Initialize client
		$client->init(self::$TRANSPORT, self::$SESSION);

		$result = $client->request('foo', array(
			'foo' => 'bar'
		));
		$this->assertTrue(is_a($result, 'stdClass'));
	}
}