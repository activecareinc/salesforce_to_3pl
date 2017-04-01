<?php

class Order_Library {
	
	/**
	 * construction
	 * initialize or set variables
	 */
	public function Order_Library() {
		
	}
	
	/**
	 * insert_sql
	 * @param array $data
	 * 
	 * @return string $sql
	 */
	public function insert_sql($data) {
		$this->validate_insert_data($data);
		$sql = "
			INSERT INTO 
			". ORDERS ."
			(
				`salesforce_order_id`,
				`tracking_number`,
				`order_expiration_date`,
				`lot_code`,
				`imei_number`,
				`carrier`,
				`shipping_service`,
				`customer`,
				`order_ref_number`,
				`ship_to_name`,
				`order_date_created`
			)
			VALUES
			(
				". $data['salesforce_order_id'] .",
				". $data['tracking_number'] .",
				". $data['order_expiration_date'] .",
				". $data['lot_code'] .",
				". $data['imei_number'] .",
				". $data['carrier'] .",
				". $data['shipping_service'] .",
				". $data['customer'] .",
				". $data['order_ref_number'] .",
				". $data['ship_to_name'] .",
				". $data['order_date_created'] ."
				
			)
		";
		
		return $sql;
	}
	
	/**
	 * validate_insert_date
	 * @param array $data
	 * @throws InvalidArgumentException when parameters doesn't meet its contract
	 * 
	 * @return void
	 */
	public function validate_insert_data($data) {
		// verify data
		if (is_array($data) !== true) {
			throw new InvalidArgumentException('Invalid $data passed. Must not be an array.');
		}
		
		if (strlen($data['salesforce_order_id']) < 1) {
			throw new InvalidArgumentException('Invalid $data["salesforce_order_id"] passed. Must not be empty.');
		}
		
		if (strlen($data['customer']) < 1) {
			throw new InvalidArgumentException('Invalid $data["customer"] passed. Must not be empty.');
		}
	}
	
	/**
	 * update_sql
	 * @param string $data
	 * 
	 * @return string $sql
	 */
	public function update_sql($data) {
		// verify data
		$this->validate_update_data($data);
		
		$sql = "
			UPDATE 
				". ORDERS ."
			SET
				". ORDERS .".tracking_number = ". $data['tracking_number'] .",
				". ORDERS .".lot_code = ". $data['lot_code'] .",
				". ORDERS .".imei_number = ". $data['imei_number'] .",
				". ORDERS .".carrier = ". $data['carrier'] .",
				". ORDERS .".shipping_service = ". $data['shipping_service'] ."
			WHERE
				". ORDERS .".salesforce_order_id = ". $data['salesforce_order_id'] ."
		";
		
		return $sql;
	}
	
	/**
	 * validate_update_data
	 * @param array $data
	 * @throws InvalidArgumentException when parameters doesn't meet its contract
	 *
	 * @return void
	 */
	public function validate_update_data($data) {
		// verify data
		if (is_array($data) !== true) {
			throw new InvalidArgumentException('Invalid $data passed. Must not be an array.');
		}
	
		if (strlen($data['salesforce_order_id']) < 1) {
			throw new InvalidArgumentException('Invalid $data["salesforce_order_id"] passed. Must not be empty.');
		}
	
		if (strlen($data['tracking_number']) < 1) {
			throw new InvalidArgumentException('Invalid $data["tracking_number"] passed. Must not be empty.');
		}
	}
	
	
	/**
	 * check_order_exists_sql
	 * @param string $salesforce_id
	 * 
	 * @return string $sql
	 */
	public function check_order_exists_sql($salesforce_id) {
		$this->validate_salesforce_order_id($salesforce_id);
		
		$sql = "
			SELECT
				". ORDERS .".*
			FROM
				". ORDERS ."
			WHERE
				". ORDERS .".salesforce_order_id = " . $salesforce_id;
		
		return $sql;
	}
	
	/**
	 * validate_salesforce_order_id
	 * @param string $salesforce_order_id
	 * @throws InvalidArgumentException when $salesforce_id doesn't meet its contract
	 * @return void
	 */
	public function validate_salesforce_order_id($salesforce_order_id) {
		if (strlen($salesforce_order_id) < 1) {
			throw new InvalidArgumentException('Invalid $salesforce_id passed. Must not be empty.');
		}
	}
	
	/**
	 * update_is_import_to_3pl_sql
	 * @param string $salesforce_order_id
	 * 
	 * @return string $sql
	 */
	public function update_is_import_to_3pl_sql($salesforce_order_id) {
		$this->validate_salesforce_order_id($salesforce_order_id);
		
		$sql = "
			UPDATE
				". ORDERS ."
			SET
				". ORDERS .".is_import_3pl = 1
			WHERE
				". ORDERS .".salesforce_order_id = ". $salesforce_order_id ."
		";
		
		return $sql;
	}
	
	/**
	 * read_list_not_import_to_3pl
	 * 
	 * @return string $sql
	 */
	public function read_list_not_import_to_3pl() {
		$sql = "
			SELECT 
				". ORDERS .".*
			FROM
				". ORDERS ."
			WHERE
				". ORDERS .".is_import_3pl = 0
		";
		
		return $sql;
	}
	
	/**
	 * read_list_import_to_salesforce
	 * 
	 * @return string $sql
	 */
	public function read_list_import_to_salesforce() {
		$sql = "
			SELECT 
				". ORDERS .".*
			FROM
				". ORDERS ."
			WHERE
				" . ORDERS . ".is_salesforce_updated = 0
			AND 
				" . ORDERS . ".tracking_number IS NOT NULL
		";
		
		return $sql;
	}
}