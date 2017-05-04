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
	 * test sending GET Request
	 * 
	 */
	public function test_get() {
		$response = $this->three_pl->get('http://secure-wms.com/orders', array('pgsiz'=>10, 'pgnum'=>1), array());
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
		$response = $this->three_pl->get('http://secure-wms.com/orders', 'pgsiz=10&pgnum=1', array());
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $additional_headers. Must be an array.
	 */
	public function test_get_invalid_headers() {
		$response = $this->three_pl->get('http://secure-wms.com/orders', array(), 'pgsiz=10&pgnum=1');
	}
	
	public function test_post() {
		$response = $this->three_pl->get('http://secure-wms.com/orders', array('pgsiz'=>10, 'pgnum'=>1), array());
		$this->assertNotNull($response);
		$this->assertTrue(is_object($response));
		$this->assertTrue(property_exists($response, 'header'));
		$this->assertTrue(property_exists($response, 'body'));
		$this->assertTrue(property_exists($response, 'original_request'));
	}

	/*
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $url. Must be a non-empty string.
	 */
	public function test_post_invalid_url() {
		$response = $this->three_pl->post('http://secure-wms.com/orders', array('pgsiz'=>10, 'pgnum'=>1), array());
	}

	/*
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $payload. Must be an array or instance of stdClass.
	 */
	public function test_post_invalid_payload() {
		$response = $this->three_pl->post('http://secure-wms.com/orders', '{json:true}', array());
	}
	
}