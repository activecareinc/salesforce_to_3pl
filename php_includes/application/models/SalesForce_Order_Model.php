<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class: SalesForce_Order_Model
 */
class SalesForce_Order_Model extends CI_Model {
	
	/**
	 * @var SalesForce $salesforce
	 */
	private $salesforce;
	
	/**
	 * default constructor
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * set salesforce
	 * @return SalesForce $salesforce;
	 */
	public function setSalesForce(SalesForce $salesforce) {
		$this->salesforce = $salesforce;
	}
	
	/**
	 * method that will retrieve all the details of an order
	 * 
	 * @return array $orders
	 */
	public function read() {
		// set the array of orders
		$orders = array();
		
		// set the query to retrieve the orders
		$orders_query = $this->salesforce_order_lib->read();
		
		// set the query to retrieve all orders with 
		// status activated and which are from 'activecare' account
		$orders_results = $this->salesforce->getClient()->query($orders_query);
		
		// get the records returned from salesforc
		$order_records = $orders_results->records;
		
		// verify if we have records
		if (count($order_records) > 0) {
			
			// loop through the results to retrieve the other details of the record
			foreach ($order_records as $order) {
				
				// select the account where the order is associated
				$account_query = $this->salesforce_order_lib->read_account_by_id($order->AccountId);
				$account = $this->salesforce->getClient()->query($account_query);
				
				// make sure we only have single record returned
				$account = reset($account->records);
				
				// verify if we have contact
				if (strlen(trim($order->Order_Contact_c__c)) > 0) {
					// select the contact where the order is associated
					$customer_query = $this->salesforce_order_lib->read_contact_by_id($order->Order_Contact_c__c);
					$customer = $this->salesforce->getClient()->query($customer_query);
					$customer = reset($customer->records);
				}
				
				// verify if we have ship to
				if (strlen(trim($order->ShipToContactId)) > 0) {
					// select the contact where the order is associated
					$ship_to_query = $this->salesforce_order_lib->read_contact_by_id($order->ShipToContactId);
					$ship_to = $this->salesforce->getClient()->query($ship_to_query);
					$ship_to = reset($ship_to->records);
				}
				
				// verify if the order belongs to activecare
				if ($account->Name === 'activecare') {
					$order_obj = new stdClass();
					$order_obj->salesforce_order_id = $order->Id;
					$order_obj->customer = $account->Name;
					$order_obj->order_ref_no = $order->OrderReferenceNumber;
					$order_obj->ship_to_name = $customer->Name;
					//$order_obj->customer = $customer->Name;
					$orders[] = $order_obj;
				}
			}
		}
		
		return $orders;
	}
	
	/**
	 * method to update order based on details from 3pl
	 * 
	 * @param stdClass $data
	 * @return void
	 */
	public function update($data) {
		// verify we have valid data to update
		if ($data instanceof stdClass !== true) {
			throw new InvalidArgumentException('Invalid parameter $data. Must be an object');
		}
		
		// update the order in salesforce
		$update = $this->salesforce->getClient()->update(array($data), 'Order');
		
		return $update;
	}
}