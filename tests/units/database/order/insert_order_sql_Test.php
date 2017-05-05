<?php
// Load db
require_once(dirname(__FILE__) . '/../../../system/config.php');

// Load library
require_once(dirname(__FILE__) . '/../../../../php_includes/application/libraries/Order_Library.php');

/**
 * Class insert_order_sql
 *
 * Tests for Order Library
 */
class insert_order_sql_Test extends PHPUnit_Framework_TestCase {

	/**
	 * @var stdClass DB class
	 */
	protected static $DB;
	
	/**
	 * 
	 * @var $order_lib
	 */
	public static $ORDER_LIB;
	
	/**
	 * 
	 * @var $test_data
	 */
	public $test_data;
	
	/**
	 * 
	 * @var $ORDER_ID
	 */
	public static $ORDER_ID;

	/**
	 * Prepare data
	 *
	 * @return void
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		// Initialize db
		self::$DB = new DB(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);
		self::$ORDER_LIB = new Order_Library();
		
	}

	/**
	 * run before exiting the class
	 *
	 * @return void
	 */
	public static function tearDownAfterClass() {
		if (self::$ORDER_ID > 0) {
			// delete 
			$sql = "DELETE FROM ". ORDERS ." WHERE ". ORDERS .".id = ".self::$ORDER_ID;
			self::$DB->query($sql);
		}
		self::$DB->close();
	}

	/**
	 * run before exiting the class
	 */
	public function setUp() {
		parent::setUp();

		$this->test_data = array();
		$this->test_data['salesforce_order_id'] = self::$DB->escape('testsf001');
		$this->test_data['tracking_number'] = self::$DB->escape('tracknum001');
		$this->test_data['order_expiration_date'] = self::$DB->escape(date('Y-m-d'));
		$this->test_data['lot_code'] = self::$DB->escape('lotcode');
		$this->test_data['imei_number'] = self::$DB->escape('testsf001');
		$this->test_data['carrier'] = self::$DB->escape('carrier');
		$this->test_data['shipping_service'] = self::$DB->escape('shipping_service');
		$this->test_data['customer'] = self::$DB->escape('Test User');
		$this->test_data['order_ref_number'] = self::$DB->escape('or1234');
		$this->test_data['ship_to_name'] = self::$DB->escape('Test User Ship');
		$this->test_data['order_date_created'] = self::$DB->escape(date('Y-m-d'));
		$this->test_data['ship_to_name'] = self::$DB->escape("Hannah Baker");
		$this->test_data['ship_to_company'] = self::$DB->escape("");
		$this->test_data['ship_to_address'] = self::$DB->escape('123 Baker St.');
		$this->test_data['ship_to_city'] = self::$DB->escape('Boston');
		$this->test_data['ship_to_state'] = self::$DB->escape('Massachusetts');
		$this->test_data['ship_to_postal_code'] = self::$DB->escape('01841');
		$this->test_data['ship_to_country'] = self::$DB->escape('US');

	}
	
	/**
	 * test_insert_order_invalid_data
	 * 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $data passed. Must not be an array.
	 * @return void
	 */
	public function test_insert_order_invalid_data() {
		self::$ORDER_LIB->insert_sql('');
	}
	
	/**
	 * test_insert_order_invalid_salesforce_id
	 * 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $data["salesforce_order_id"] passed. Must not be empty.
	 * @return void
	 */
	public function test_insert_order_invalid_salesforce_id() {
		$this->test_data['salesforce_order_id'] = '';
		self::$ORDER_LIB->insert_sql($this->test_data);
	}
	
	/**
	 * test_insert_order_invalid_customer
	 * 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $data["customer"] passed. Must not be empty.
	 * @return void
	 */
	public function test_insert_order_invalid_customer() {
		$this->test_data['customer'] = '';
		self::$ORDER_LIB->insert_sql($this->test_data);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $data["ship_to_name"] passed. Must not be empty.
	 * @return void
	 */
	public function test_insert_order_invalid_ship_to_name() {
		$this->test_data['ship_to_name'] = '';
		self::$ORDER_LIB->insert_sql($this->test_data);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $data["ship_to_address"] passed. Must not be empty.
	 * @return void
	 */
	public function test_insert_order_invalid_ship_to_address() {
		$this->test_data['ship_to_address'] = '';
		self::$ORDER_LIB->insert_sql($this->test_data);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $data["ship_to_city"] passed. Must not be empty.
	 * @return void
	 */
	public function test_insert_order_invalid_ship_to_city() {
		$this->test_data['ship_to_city'] = '';
		self::$ORDER_LIB->insert_sql($this->test_data);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $data["ship_to_state"] passed. Must not be empty.
	 * @return void
	 */
	public function test_insert_order_invalid_ship_to_state() {
		$this->test_data['ship_to_state'] = '';
		self::$ORDER_LIB->insert_sql($this->test_data);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $data["ship_to_postal_code"] passed. Must not be empty.
	 * @return void
	 */
	public function test_insert_order_invalid_ship_to_postal_code() {
		$this->test_data['ship_to_postal_code'] = '';
		self::$ORDER_LIB->insert_sql($this->test_data);
	}
	
	/**
	 * test_insert_order
	 * 
	 * @return void
	 */
	public function test_insert_order() {
		$sql = self::$ORDER_LIB->insert_sql($this->test_data);
		self::$DB->query($sql);
		self::$ORDER_ID = (int)self::$DB->insert_id();
	}
	
	/**
	 * test_check_order_exist_invalid_data
	 * 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $salesforce_id passed. Must not be empty.
	 * @return void
	 */
	public function test_check_order_exist_invalid_data() {
		self::$ORDER_LIB->check_order_exists_sql('');
	}
	
	/**
	 * test_check_order_exist_no_record
	 * 
	 * @return void
	 */
	public function test_check_order_exist_no_record() {
		$sql = self::$ORDER_LIB->check_order_exists_sql(self::$DB->escape('asd'));
		$query = self::$DB->query($sql);
		
		$this->assertTrue((int)$query->num_rows == 0);
	}
	
	/**
	 * test_check_order_exist
	 * 
	 * @return void
	 */
	public function test_check_order_exist() {
		$sql = self::$ORDER_LIB->check_order_exists_sql(self::$DB->escape('testsf001'));
		$query = self::$DB->query($sql);
		$result = $query->fetch_object(); 
		$this->assertEquals($result->salesforce_order_id, 'testsf001');
		$this->assertEquals($result->customer, 'Test User');
	}
}