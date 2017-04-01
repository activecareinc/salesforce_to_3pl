<?php

/**
 * Class: SalesForce_Order_Model
 */
class SalesForce_Order_Model {
	
	/**
	 * @var SalesForce $salesforce
	 */
	private $salesforce
	
	/**
	 * default constructor
	 */
	public function __construct(SalesForce $salesforce) {
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
				
				// set the query to retrieve account details
				$account_query = $this->salesforce_order_lib->read_account_by_id($order->AccountId);
				
				// select the account where the order is associated
				$account = $this->salesforce->getClient()->query($account_query);
				
				// make sure we only have single record returned
				$account = reset($account);
				
				// set the query to retrieve the contact details
				$customer_query = $this->salesforce_order_lib->read_contact_by_id($order->Order_Contact_c__c);
				
				// select the contact where the order is associated
				$customer = $this->salesforce->getClient()->query($customer_query);
				
				// query to retrieve ship_to
				$ship_to_query = $this->salesforce_order_lib->read_contact_by_id($order->ShipToContactId);
				
				// select the contact where the order is associated
				$ship_to = $this->salesforce->getClient()->query($ship_to_query);
				
				// make sure we only have single record returned
				$contact = reset($account);
				
				// verify if the order belongs to activecare
				if ($account->Name === 'activecare') {
					$order_obj = new stdClass();
					$order_obj->salesforce_order_id = $order->Id;
					$order_obj->order_ref_no = $order->OrderReferenceNumber;
					$order_obj->ship_to = $ship_to->Name;
					$order_obj->customer = $customer->Name;
					$orders[] = $order_obj;
				}
			}
		}
		
		return $orders;
	}
}