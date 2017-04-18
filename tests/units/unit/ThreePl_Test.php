<?php
use \ThreePlCentral\Order\OrderRepository;
use \ThreePlCentral\RequestFactory;
use \ThreePlCentral\ThreePlCentral;
use \ThreePlCentral\Exception;

require_once(dirname(__FILE__) . '/../../system/config.php');


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
	
	/**
	 * test to retrieve orders from 3PL
	 */
	public function test_retrieve_orders() {
		$start_date = ((new \DateTime())->modify('-30 days'));
		$end_date = new \DateTime();
		
		$response = OrderRepository::findOrders($this->three_pl, $start_date, $end_date);
	}
}