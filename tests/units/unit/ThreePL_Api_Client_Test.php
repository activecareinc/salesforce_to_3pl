<?php
use \ThreePlCentral\Order\OrderRepository;
use \ThreePlCentral\RequestFactory;
use \ThreePlCentral\ThreePlCentral;
use \ThreePlCentral\Exception;

require_once(dirname(__FILE__) . '/../../system/config.php');
require_once(PROJECT_DIR_PATH . 'php_includes/application/libraries/ThreePL_API_Client.php');


/**
 * ThreePl_Test
 *
 */
class ThreePl_Api_Client_Test extends PHPUnit_Framework_TestCase {
	
	/**
	 * 
	 * @var array
	 */
	public $test_data;
	
	/**
	 * 
	 * @var unknown_type
	 */
	public $three_pl;
		
	/**
	 * run first before anything else
	 *
	 * @return void
	 */
	public static function setUpBeforeClass() {
	
	}
	
	/**
	 * runs every before test case
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		
		$this->three_pl = new ThreePL_Api_Client();
	}
	
	/**
	 * run every after test case
	 *
	 * @return void
	 */
	public function tearDown() {
	}
	
	/**
	 * run before exiting the class
	 *
	 * @return void
	 */
	public static function tearDownAfterClass() {
	}

	/**
	 * Test sending a post request to an invalid url 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $body. Must not be empty on POST requests
	 */
	public function test_send_invalid_body() {
		$response = $this->three_pl->send('POST', '/token',null, array(), array());
	}
	
	/**
	 * test sending GET Request
	 * 
	 */
	public function test_get() {
		$response = $this->three_pl->get('/orders', array('pgsiz'=>10, 'pgnum'=>1), array());
		$this->assertNotNull($response);
		$this->assertTrue(is_object($response));
		$this->assertTrue(property_exists($response, 'header'));
		$this->assertTrue(property_exists($response, 'body'));
		$this->assertTrue(property_exists($response, 'original_request'));
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $url. Must be a non-empty string.
	 */
	public function test_get_invalid_url() {
		$response = $this->three_pl->get('', array('pgsiz'=>10, 'pgnum'=>1), array());
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $query. Must be an array.
	 */
	public function test_get_invalid_query() {
		$response = $this->three_pl->get('/orders', 'pgsiz=10&pgnum=1', array());
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $additional_headers. Must be an array.
	 */
	public function test_get_invalid_headers() {
		$response = $this->three_pl->get('/orders', array(), 'pgsiz=10&pgnum=1');
	}
	
	public function test_post() {
		$response = $this->three_pl->post('/orders', array('pgsiz'=>10, 'pgnum'=>1), array());
		$this->assertNotNull($response);
		$this->assertTrue(is_object($response));
		$this->assertTrue(property_exists($response, 'header'));
		$this->assertTrue(property_exists($response, 'body'));
		$this->assertTrue(property_exists($response, 'original_request'));
	}

	/**
	 * Test sending a post request to an invalid url 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $url. Must be a non-empty string.
	 */
	public function test_post_invalid_url() {
		$response = $this->three_pl->post('', array('pgsiz'=>10, 'pgnum'=>1), array());
	}

	/**
	 * Test sending a post request with an invalid payload
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $payload. Must be an array or instance of stdClass.
	 */
	public function test_post_invalid_payload() {
		$response = $this->three_pl->post('/orders', '{json:true}', array());
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $additional_headers. Must be an array.
	 */
	public function test_post_invalid_headers() {
		$response = $this->three_pl->post('/orders', array(), 'pgsiz=10&pgnum=1');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $key. Must be a non-empty string.
	 */
	public function test_authenticate_invalid_key() {
		$response = $this->three_pl->authenticate('', '1');
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $login_id. Must be a non-empty string.
	 */
	public function test_authenticate_invalid_login () {
		$response = $this->three_pl->authenticate('{1231}', '');
	}

	/**
	 * Test sending an authentication request
	 */
	public function test_authenticate() {
		$response = $this->three_pl->authenticate(THREE_PL_KEY, FACILITY_ID);

		$this->assertNotNull($response);
		$this->assertTrue(is_object($response));
		$this->assertTrue(property_exists($response, 'access_token'));
		$this->assertTrue(property_exists($response, 'token_type'));
	}

	/**
	 * Test sending request to create an order
	 * @depends test_authenticate
	 */
	public function test_create_order() {

		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => CUSTOMER_ID,
			'order_ref_number' => 'TEST ORDER ' . time(),
			'carrier' => 'UPS',
			'service_level' => 'Ground',
			'ship_to_company' => 'Bridge',
			'ship_to_name' => 'Engineer Test',
			'ship_to_address' => '123 Main St.',
			'ship_to_city' => 'Hayward',
			'ship_to_state' => 'CA',
			'ship_to_postal_code' => '95454',
			'ship_to_country' => 'US',
		);

		$token = $this->three_pl->authenticate(THREE_PL_KEY, FACILITY_ID)->access_token;
		$response = $this->three_pl->create_order($data, $token);

	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'customer_identifier'. Must be an non-empty string
	 */
	public function test_create_order_invalid_customer_id () {
		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => '',
			'order_ref_number' => 'TEST ORDER ' . time(),
			'carrier' => 'UPS',
			'service_level' => 'Ground',
			'ship_to_company' => 'Bridge',
			'ship_to_name' => 'Engineer Test',
			'ship_to_address' => '123 Main St.',
			'ship_to_city' => 'Hayward',
			'ship_to_state' => 'CA',
			'ship_to_postal_code' => '95454',
			'ship_to_country' => 'US',
		);
		$response = $this->three_pl->create_order($data, '123');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'facility_identifier'. Must be an non-empty string
	 */
	public function test_create_order_invalid_facility_id () {
		$data = array(
			'facility_identifier' => '',
			'customer_identifier' => CUSTOMER_ID,
			'order_ref_number' => 'TEST ORDER ' . time(),
			'carrier' => 'UPS',
			'service_level' => 'Ground',
			'ship_to_company' => 'Bridge',
			'ship_to_name' => 'Engineer Test',
			'ship_to_address' => '123 Main St.',
			'ship_to_city' => 'Hayward',
			'ship_to_state' => 'CA',
			'ship_to_postal_code' => '95454',
			'ship_to_country' => 'US',
		);
		$response = $this->three_pl->create_order($data, '123');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'order_ref_number'. Must be an non-empty string
	 */
	public function test_create_order_invalid_reference_number () {
		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => CUSTOMER_ID,
			'carrier' => 'UPS',
			'service_level' => 'Ground',
			'ship_to_company' => 'Bridge',
			'ship_to_name' => 'Engineer Test',
			'ship_to_address' => '123 Main St.',
			'ship_to_city' => 'Hayward',
			'ship_to_state' => 'CA',
			'ship_to_postal_code' => '95454',
			'ship_to_country' => 'US',
		);
		$response = $this->three_pl->create_order($data, '123');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'carrier'. Must be an non-empty string
	 */
	public function test_create_order_invalid_carrier () {
		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => CUSTOMER_ID,
			'order_ref_number' => '12315413',
			'service_level' => 'Ground',
			'ship_to_company' => 'Bridge',
			'ship_to_name' => 'Engineer Test',
			'ship_to_address' => '123 Main St.',
			'ship_to_city' => 'Hayward',
			'ship_to_state' => 'CA',
			'ship_to_postal_code' => '95454',
			'ship_to_country' => 'US',
		);
		$response = $this->three_pl->create_order($data, '123');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'service_level'. Must be an non-empty string
	 */
	public function test_create_order_invalid_service_level () {
		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => CUSTOMER_ID,
			'order_ref_number' => '12315413',
			'carrier' => 'UPS',
			'ship_to_company' => 'Bridge',
			'ship_to_name' => 'Engineer Test',
			'ship_to_address' => '123 Main St.',
			'ship_to_city' => 'Hayward',
			'ship_to_state' => 'CA',
			'ship_to_postal_code' => '95454',
			'ship_to_country' => 'US',
		);
		$response = $this->three_pl->create_order($data, '123');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'ship_to_company'. Must be an non-empty string
	 */
	public function test_create_order_invalid_ship_to_company () {
		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => CUSTOMER_ID,
			'order_ref_number' => '12315413',
			'carrier' => 'UPS',
			'service_level' => 'Ground',
			'ship_to_name' => 'Engineer Test',
			'ship_to_address' => '123 Main St.',
			'ship_to_city' => 'Hayward',
			'ship_to_state' => 'CA',
			'ship_to_postal_code' => '95454',
			'ship_to_country' => 'US',
		);
		$response = $this->three_pl->create_order($data, '123');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'ship_to_name'. Must be an non-empty string
	 */
	public function test_create_order_invalid_ship_to_name() {
		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => CUSTOMER_ID,
			'order_ref_number' => '12315413',
			'carrier' => 'UPS',
			'service_level' => 'Ground',
			'ship_to_company' => 'Bridge',
			'ship_to_address' => '123 Main St.',
			'ship_to_city' => 'Hayward',
			'ship_to_state' => 'CA',
			'ship_to_postal_code' => '95454',
			'ship_to_country' => 'US',
		);
		$response = $this->three_pl->create_order($data, '123');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'ship_to_address'. Must be an non-empty string
	 */
	public function test_create_order_invalid_ship_to_address() {
		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => CUSTOMER_ID,
			'order_ref_number' => '12315413',
			'carrier' => 'UPS',
			'service_level' => 'Ground',
			'ship_to_company' => 'Bridge',
			'ship_to_name' => 'Engineer',
			'ship_to_city' => 'Hayward',
			'ship_to_state' => 'CA',
			'ship_to_postal_code' => '95454',
			'ship_to_country' => 'US',
		);
		$response = $this->three_pl->create_order($data, '123');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'ship_to_city'. Must be an non-empty string
	 */
	public function test_create_order_invalid_ship_to_city() {
		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => CUSTOMER_ID,
			'order_ref_number' => '12315413',
			'carrier' => 'UPS',
			'service_level' => 'Ground',
			'ship_to_company' => 'Bridge',
			'ship_to_name' => 'Engineer',
			'ship_to_address' => '123 Main St.',
			'ship_to_state' => 'CA',
			'ship_to_postal_code' => '95454',
			'ship_to_country' => 'US',
		);
		$response = $this->three_pl->create_order($data, '123');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'ship_to_state'. Must be an non-empty string
	 */
	public function test_create_order_invalid_ship_to_state() {
		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => CUSTOMER_ID,
			'order_ref_number' => '12315413',
			'carrier' => 'UPS',
			'service_level' => 'Ground',
			'ship_to_company' => 'Bridge',
			'ship_to_name' => 'Engineer',
			'ship_to_address' => '123 Main St.',
			'ship_to_city' => 'Hayward',
			'ship_to_postal_code' => '95454',
			'ship_to_country' => 'US',
		);
		$response = $this->three_pl->create_order($data, '123');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'ship_to_postal_code'. Must be an non-empty string
	 */
	public function test_create_order_invalid_ship_to_postal_code() {
		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => CUSTOMER_ID,
			'order_ref_number' => '12315413',
			'carrier' => 'UPS',
			'service_level' => 'Ground',
			'ship_to_company' => 'Bridge',
			'ship_to_name' => 'Engineer',
			'ship_to_address' => '123 Main St.',
			'ship_to_city' => 'Hayward',
			'ship_to_state' => 'CA',
			'ship_to_country' => 'US',
		);
		$response = $this->three_pl->create_order($data, '123');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter 'ship_to_country'. Must be an non-empty string
	 */
	public function test_create_order_invalid_ship_to_country() {
		$data = array(
			'facility_identifier' => FACILITY_ID,
			'customer_identifier' => CUSTOMER_ID,
			'order_ref_number' => '12315413',
			'carrier' => 'UPS',
			'service_level' => 'Ground',
			'ship_to_company' => 'Bridge',
			'ship_to_name' => 'Engineer',
			'ship_to_address' => '123 Main St.',
			'ship_to_city' => 'Hayward',
			'ship_to_postal_code' => '95454',
			'ship_to_state' => 'CA',
		);
		$response = $this->three_pl->create_order($data, '123');
	}
}