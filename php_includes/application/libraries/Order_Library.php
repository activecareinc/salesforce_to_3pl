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
	 * 
	 * @return string $sql
	 */
	public function update_sql() {
		
	}
	
	/**
	 * validate_update_data
	 * 
	 * @return void
	 */
	public function validate_update_data() {
		
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
}