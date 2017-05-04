<?php

/**
 * Class: SalesForce_Order_Library
 */
class SalesForce_Order_Library {
	
	/**
	 * retrieves all orders from SalesForce with activated status
	 * 
	 * @return string $sql
	 */
	public function read() {
		// prepare the query to retrieve orders from salesforce
		$query = "
				SELECT 
					Id, 
					AccountId, 
					CreatedDate, 
					Order_Contact__c, 
					OrderReferenceNumber, 
					Status, 
					ShipToContactId, 
					Tracking_Number__c, 
					Shipping_Service__c, 
					Shipping_Service_Field__c,
					ShippingCity,
					ShippingCountry,
					ShippingPostalCode,
					ShippingState,
					ShippingStreet,
					EndDate
				FROM 
					Order 
				WHERE 
					Status = 'Activated'
		";
		
		return $query;
		
	}
	
	/**
	 * retrieves details of an account
	 * 
	 * @param string $account_id
	 * @throws InvalidArgumentException if parameter $account_id is invalid
	 * @return string $query
	 */
	public function read_account_by_id($account_id) {
		// verify if we have valid parameter
		$this->validate_read_account_by_id($account_id);
		
		// prepare the query
		$query = "
			SELECT 
				Id, Name
			FROM
				Account
			WHERE
				Id = '" . $account_id . "'
		";
		
		return $query;
	}
	
	/**
	 * retrieves details of a contact
	 * 
	 * @param string $contact_id
	 * @throws InvalidArgumentException if parameter $contact_id is invalid
	 * @return string $query
	 */
	public function read_contact_by_id($contact_id) {
		// verify if we have valid parameter
		$this->validate_read_contact_by_id($contact_id);
		
		// prepare query
		$query = "
			SELECT
				Id, Name
			FROM
				Contact
			WHERE
				Id = '" . $contact_id . "'
		";
		
		return $query;
	}
	
	/**
	 * validates the required parameters for read_account_by_id method
	 * @throws InvalidArgumentException if parameter $account_id is invalid
	 * @return void
	 */
	public function validate_read_account_by_id($account_id) {
		if (isset($account_id) !== true || strlen(trim($account_id)) < 1) {
			throw new InvalidArgumentException('Invalid parameter $account_id. Must be a string');
		}
	}
	
	/**
	 * validates the required parameters for read_contact_by_id method
	 * @throws InvalidArgumentException if parameter $contact_id is invalid
	 * @return void
	 */
	public function validate_read_contact_by_id($contact_id) {
		if (isset($contact_id) !== true || strlen(trim($contact_id)) < 1) {
			throw new InvalidArgumentException('Invalid parameter $contact_id. Must be a string');
		}
	}
	
	/**
	 * retrieves the order items associated to an order
	 * 
	 * @param string $order_id
	 * @throws InvalidArgumentException if parameter $order_id is invalid
	 * @return string $query
	 */
	public function read_order_items_by_order_id($order_id) {
		
		// validate required parameter
		$this->validate_read_order_items_by_order_id($order_id);
		
		$query = "
			SELECT 
				Product2Id
			FROM
				OrderItem
			WHERE
				OrderId = '" . $order_id . "'
		";
		
		return $query;
	}
	
	/**
	 * retrieves the details of a product
	 * 
	 * @param string $order_item_id
	 * @throws InvalidArgumentException if parameter $product_id is invalid
	 * @return string $query
	 */
	public function read_product_by_id($product_id) {
		
		// validate required parameter
		$this->validate_read_product_by_id($product_id);
		
		$query = "
			SELECT
				ID__c,
				Lot_Code__c,
				IMEI_Number__c
			FROM
				Product2
			WHERE
				Id = '" . $product_id . "'
		";
		
		return $query;
	}
	
	/**
	 * validates the parameter for read_order_items_by_order_id method
	 * 
	 * @param string $order_id
	 * @throws InvalidArgumentException if parameter $order_id is invalid
	 * @return void
	 */
	public function validate_read_order_items_by_order_id($order_id) {
		if (isset($order_id) !== true || is_string($order_id) !== true || strlen(trim($order_id)) < 1) {
			throw new InvalidArgumentException('Invalid parameter $order_id. Must be a valid string.');
		}
	}
	
	/**
	 * validates the parameter for read_product_by_id method
	 * 
	 * @param string $product_id
	 * @throws InvalidArgumentException if parameter $product_id is invalid
	 * @return void
	 */
	public function validate_read_product_by_id($product_id) {
		if (isset($product_id) !== true || is_string($product_id) !== true || strlen(trim($product_id)) < 1) {
			throw new InvalidArgumentException('Invalid parameter $product_id. Must be a valid string.');
		}
	}
}