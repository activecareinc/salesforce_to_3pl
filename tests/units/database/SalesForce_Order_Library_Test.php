<?php

require_once(__DIR__ . '/../../system/config.php');

require_once(__DIR__ . '/../../../php_includes/application/libraries/SalesForce_Order_Library.php');
require_once(__DIR__ . '/../../../php_includes/application/libraries/SalesForce/inclusions.php');

/**
 * verify the methods to communicate to SalesForce
 */
class SalesForce_Order_Library_Test extends PHPUnit_Framework_TestCase {

	/**
	 * @var SalesForce $salesforce
	 */
	public static $salesForce;
	
	/**
	 * default @setTup
	 * @return void
	 */
	public static function setUpBeforeClass() {
		// setup salesforce
		$client = new SforceEnterpriseClient();

		self::$salesForce = new SalesForce($client);
		self::$salesForce->authenticate(SALESFORCE_USERNAME, SALESFORCE_PASSWORD, SALESFORCE_TOKEN);
	}

	/**
	 * test to verify read_account_by_id if parameter is invalid
	 * 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $account_id. Must be a string
	 * @return void
	 */
	public function test_read_account_by_id_invalid_id() {
		$salesforce_library = new SalesForce_Order_Library();
		$salesforce_library->read_account_by_id('');
	}
	
	/**
	 * test to verify read_contact_by_id if parameter is invalid
	 * 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $contact_id. Must be a string
	 * @return void
	 */
	public function test_read_contact_by_id_invalid_id() {
		$salesforce_library = new SalesForce_Order_Library();
		$salesforce_library->read_contact_by_id('');
	}
	
	/**
	 * test to verify query to find orders
	 * @return void
	 */
	public function test_read() {
		$salesforce_library = new SalesForce_Order_Library();
		$query = $salesforce_library->read();
		
		$orders = self::$salesForce->getClient()->query($query);
		
		// verify we have records returned
		$this->assertTrue(count($orders->records) > 0);
	}
	
	/**
	 * test to verify query to find account by id
	 * @return void
	 */
	public function test_read_account_by_id() {
		$salesforce_library = new SalesForce_Order_Library();
		$query = $salesforce_library->read_account_by_id('001E000001RNKp8IAH');
		$account = self::$salesForce->getClient()->query($query);
		
		// reset the record
		$account = reset($account->records);
		
		// verify we have records returned
		$this->assertEquals($account->Id, '001E000001RNKp8IAH');
	}
	
	/**
	 * test to verify query to find contact by id
	 * @return void
	 */
	public function test_read_contact_by_id() {
		$salesforce_library = new SalesForce_Order_Library();
		$query = $salesforce_library->read_contact_by_id('0034400001lAFZZAA4');
		$contact = self::$salesForce->getClient()->query($query);
		
		// reset the record
		$contact = reset($contact->records);
		
		// verify we have records returned
		$this->assertEquals($contact->Id, '0034400001lAFZZAA4');
		$this->assertEquals($contact->Name, 'TONYA COVELLO');
	}
	
	/**
	 * test to verify read_order_items_by_order_id method if param is invalid
	 * 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $order_id. Must be a valid string.
	 * @return void
	 */
	public function test_read_order_items_by_order_id_invalid_id() {
		$salesforce_library = new SalesForce_Order_Library();
		$salesforce_library->read_order_items_by_order_id('');
	}
	
	/**
	 * test to verify read_order_items_by_order_id method if param is invalid
	 * 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $product_id. Must be a valid string.
	 * @return void
	 */
	public function test_read_product_by_id_invalid_id() {
		$salesforce_library = new SalesForce_Order_Library();
		$salesforce_library->read_product_by_id('');
	}
	
	/**
	 * test to verify read_order_items_by_order_id method
	 * @return void
	 */
	public function test_read_order_items_by_order_id() {
		$salesforce_library = new SalesForce_Order_Library();
		$query = $salesforce_library->read_order_items_by_order_id('801Q00000002y0EIAQ');
		$order_items = self::$salesForce->getClient()->query($query);

		// verify there are records returned
		$order_items = reset($order_items->records);
		$this->assertEquals('01t4400000AZ4vWAAT', $order_items->Product2Id);
	}
	
	/**
	 * test to verify read_product_by_id method
	 * @return void
	 */
	public function test_read_product_by_id() {
		$salesforce_library = new SalesForce_Order_Library();
		$query = $salesforce_library->read_product_by_id('01t4400000AZ4vWAAT');
		$product = self::$salesForce->getClient()->query($query);

		// verify there are records returned
		$product = reset($product->records);
		$this->assertEquals('01t4400000AZ4vWAAT', $product->ID__c);
	}
	
}