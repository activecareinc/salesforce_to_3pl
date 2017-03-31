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
					Id, AccountId, 
					CreatedDate, Order_Contact_c__c, 
					OrderReferenceNumber, 
					Status, ShipToContactId 
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
}