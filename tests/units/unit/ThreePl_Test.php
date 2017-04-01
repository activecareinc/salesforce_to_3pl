<?php
// Load db
use ThreePlCentral\Order\OrderRepository;
use ThreePlCentral\ThreePlCentral;

require_once(dirname(__FILE__) . '/../../system/config.php');

// Load library
require_once(dirname(__FILE__) . '/../../../php_includes/application/libraries/3PL/php-3pl-central-master/src/Order/OrderRepository.php');
require_once(dirname(__FILE__) . '/../../../php_includes/application/libraries/3PL/php-3pl-central-master/src/ThreePlCentral.php');
require_once(dirname(__FILE__) . '/../../../php_includes/application/libraries/3PL/php-3pl-central-master/src/Exception.php');


/**
 * ThreePl_Test
 *
 */
class ThreePl_Test extends PHPUnit_Framework_TestCase {
	
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
		$this->test_data = array();
		$this->test_data['order_ref_number'] = 'or001';
		$this->test_data['customer'] = 'Test Customer';
		$this->test_data['ship_to_name'] = 'Ship Name';
		
		$this->three_pl = new ThreePlCentral(THREE_PL_KEY, CUSTOMER_ID, FACILITY_ID, USERNAME, PASSWORD);
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
	 * test_order_create_invalid_order_ref
	 * 
	 * @expectedException Exception
	 * @expectedExceptionMessage Invalid order_ref_number passed. Must be string with value.
	 */
	public function test_order_create_invalid_order_ref() {
		$this->test_data['order_ref_number'] = '';
		OrderRepository::createOrder($this->three_pl, $this->test_data);
	}
	
	/**
	 * test_order_create_invalid_ship_to_name
	 *
	 * @expectedException Exception
	 * @expectedExceptionMessage Invalid ship_to_name passed. Must be string with value.
	 */
	public function test_order_create_invalid_ship_to_name() {
		$this->test_data['ship_to_name'] = '';
		OrderRepository::createOrder($this->three_pl, $this->test_data);
	}
	
	/**
	 * test_order_create_invalid_customer
	 *
	 * @expectedException Exception
	 * @expectedExceptionMessage Invalid customer passed. Must be string with value.
	 */
	public function test_order_create_invalid_customer() {
		$this->test_data['customer'] = '';
		OrderRepository::createOrder($this->three_pl, $this->test_data);
	}
}