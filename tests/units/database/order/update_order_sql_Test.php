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
class update_order_sql_Test extends PHPUnit_Framework_TestCase {

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
	 * @var $test_update_data
	 */
	public $test_update_data;
	
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
		$this->test_data['threepl_order_id'] = 123456;
		$this->test_data['tracking_number'] = self::$DB->escape('');
		$this->test_data['order_expiration_date'] = self::$DB->escape(date('Y-m-d'));
		$this->test_data['lot_code'] = self::$DB->escape('');
		$this->test_data['imei_number'] = self::$DB->escape('');
		$this->test_data['carrier'] = self::$DB->escape('');
		$this->test_data['shipping_service'] = self::$DB->escape('');
		$this->test_data['customer'] = self::$DB->escape('Test User');
		$this->test_data['order_ref_number'] = self::$DB->escape('or1234');
		$this->test_data['ship_to_name'] = self::$DB->escape('Test User Ship');
		$this->test_data['order_date_created'] = self::$DB->escape(date('Y-m-d'));
		$this->test_data['ship_to_company'] = self::$DB->escape("");
		$this->test_data['ship_to_address'] = self::$DB->escape('123 Baker St.');
		$this->test_data['ship_to_city'] = self::$DB->escape('Boston');
		$this->test_data['ship_to_state'] = self::$DB->escape('Massachusetts');
		$this->test_data['ship_to_postal_code'] = self::$DB->escape('01841');
		$this->test_data['ship_to_country'] = self::$DB->escape('US');
		
		$this->test_update_data = array();
		$this->test_update_data['tracking_number'] = self::$DB->escape('track_num001');
		$this->test_update_data['threepl_order_id'] = 123456;
		$this->test_update_data['lot_code'] = self::$DB->escape('lotcode');
		$this->test_update_data['imei_number'] = self::$DB->escape('testsf001');
		$this->test_update_data['carrier'] = self::$DB->escape('carrier');
		$this->test_update_data['shipping_service'] = self::$DB->escape('shipping_service');
	}
	
	/**
	 * test_insert_order
	 * 
	 * @return void
	 */
	public function insert_order() {
		$sql = self::$ORDER_LIB->insert_sql($this->test_data);
		self::$DB->query($sql);
		self::$ORDER_ID = (int)self::$DB->insert_id();
	}
	
	/**
	 * test_update_order_invalid_data
	 * 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $data passed. Must not be an array.
	 * @return void
	 */
	public function test_update_order_invalid_data() {
		self::$ORDER_LIB->update_sql('');
	}
	
	/**
	 * test_update_order_invalid_tracking_number
	 *
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $data["tracking_number"] passed. Must not be empty.
	 * @return void
	 */
	public function test_update_order_invalid_tracking_number() {
		$this->test_update_data['tracking_number'] = '';
		self::$ORDER_LIB->update_sql($this->test_update_data);
	}
	
	/**
	 * test_update_order_invalid_salesforce_order_id
	 *
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $data["threepl_order_id"] passed. Must not be empty.
	 * @return void
	 */
	public function test_update_order_invalid_salesforce_order_id() {
		$this->test_update_data['threepl_order_id'] = null;
		self::$ORDER_LIB->update_sql($this->test_update_data);
	}
	
	/**
	 * test_update_order
	 *
	 * @return void
	 */
	public function test_update_order() {
		$this->insert_order();
		
		$sql = self::$ORDER_LIB->update_sql($this->test_update_data);
		self::$DB->query($sql);
		
		// read data
		$sql = "SELECT ". ORDERS .".* FROM ". ORDERS ." WHERE ". ORDERS .".id = " . self::$ORDER_ID;
		$query = self::$DB->query($sql);
		$this->assertTrue((int)$query->num_rows > 0);
	}
	
	/**
	 * test_is_import_3pl_invalid_salesforce_order_id
	 *
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $salesforce_id passed. Must not be empty.
	 * @return void
	 */
	public function test_is_import_3pl_invalid_salesforce_order_id() {
		self::$ORDER_LIB->update_is_import_to_3pl_sql('', 123456);
	}
	
	/**
	 * test_is_import_3pl
	 * 
	 * @return void
	 */
	public function test_is_import_3pl() {
		// read data
		$sql = "SELECT ". ORDERS .".* FROM ". ORDERS ." WHERE ". ORDERS .".id = " . self::$ORDER_ID;
		$query = self::$DB->query($sql);
		$result = $query->fetch_object();
		$this->assertEquals($result->is_import_3pl, 0);
		
		// update is import
		$sql = self::$ORDER_LIB->update_is_import_to_3pl_sql(self::$DB->escape('testsf001'), 123456);
		self::$DB->query($sql);
		
		// read data
		$sql1 = "SELECT ". ORDERS .".* FROM ". ORDERS ." WHERE ". ORDERS .".id = " . self::$ORDER_ID;
		$query1 = self::$DB->query($sql1);
		$result1 = $query1->fetch_object();
		$this->assertEquals($result1->is_import_3pl, 1);
	}
	
	/**
	 * test_read_not_import
	 * 
	 * @return void
	 */
	public function test_read_not_import() {
		// read data
		$sql = self::$ORDER_LIB->read_list_not_import_to_3pl();
		$query = self::$DB->query($sql);
		$this->assertTrue((int)$query->num_rows === 0);
		
		// update data
		$sql1 = "
			UPDATE
				". ORDERS ."
			SET
				". ORDERS .".is_import_3pl = 0
			WHERE
				". ORDERS .".salesforce_order_id = ". self::$DB->escape('testsf001') ."
		";
		self::$DB->query($sql1);
		
		$sql1 = self::$ORDER_LIB->read_list_not_import_to_3pl();
		$query1 = self::$DB->query($sql1);
		$this->assertTrue((int)$query1->num_rows > 0);
	}
	
	/**
	 * test_read_list_import_to_salesforce
	 * 
	 * @return void
	 */
	public function test_read_list_import_to_salesforce() {
		// read data
		$sql = self::$ORDER_LIB->read_list_import_to_salesforce();
		$query = self::$DB->query($sql);
		$this->assertTrue((int)$query->num_rows === 1);
	}
	
	/**
	 * test_is_salesforce_updated_invalid_salesforce_id
	 *
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid $salesforce_id passed. Must not be empty.
	 * @return void
	 */
	public function test_is_salesforce_updated_invalid_salesforce_id() {
		self::$ORDER_LIB->update_is_salesforce_updated_sql('');
	}
	
	/**
	 * test_is_salesforce_updated
	 * 
	 * @return void
	 */
	public function test_is_salesforce_updated() {
		// read data
		$sql = "SELECT ". ORDERS .".* FROM ". ORDERS ." WHERE ". ORDERS .".id = " . self::$ORDER_ID;
		$query = self::$DB->query($sql);
		$result = $query->fetch_object();
		$this->assertEquals($result->is_salesforce_updated, 0);
		
		// update is import
		$sql = self::$ORDER_LIB->update_is_salesforce_updated_sql(self::$DB->escape('testsf001'));
		self::$DB->query($sql);
		
		// read data
		$sql1 = "SELECT ". ORDERS .".* FROM ". ORDERS ." WHERE ". ORDERS .".id = " . self::$ORDER_ID;
		$query1 = self::$DB->query($sql1);
		$result1 = $query1->fetch_object();
		$this->assertEquals($result1->is_salesforce_updated, 1);
	}
}