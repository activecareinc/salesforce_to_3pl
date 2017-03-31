<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Order_Model extends CI_Model {
	
	/**
	 * Order_Model
	 */
	public function Order_Model() {
		
	}
	
	/**
	 * insert
	 * @param $data
	 * @throws InvalidArgumentException when parameters doesn't meet its contract
	 * @throws RuntimeException when there is an error in executing the query
	 * 
	 * @return int id
	 */
	public function insert($data) {
		// Verify data
		$this->order_lib->validate_insert_data($data);
		
		// Sanitize user data
		$data['salesforce_order_id'] = $this->db->escape($data['salesforce_order_id']);
		$data['tracking_number'] = $this->db->escape($data['tracking_number']);
		$data['order_expiration_date'] = $this->db->escape($data['order_expiration_date']);
		$data['lot_code'] = $this->db->escape($data['lot_code']);
		$data['imei_number'] = $this->db->escape($data['imei_number']);
		$data['carrier'] = $this->db->escape($data['carrier']);
		$data['shipping_service'] = $this->db->escape($data['shipping_service']);
		$data['customer'] = $this->db->escape($data['customer']);
		$data['order_ref_number'] = $this->db->escape($data['order_ref_number']);
		$data['ship_to_name'] = $this->db->escape($data['ship_to_name']);
		$data['order_date_created'] = $this->db->escape($data['order_date_created']);
		
		// Prepare user sql
		$sql = $this->order_lib->insert_sql($data);
		
		// execute the query
		$query = $this->db->query($sql);
		
		// verify there was no error in the query
		if (is_object($query) !== true) {
			throw new RuntimeException(EXCEPTION_RUNTIME);
		}
		
		return  $this->db->insert_id;
	}
	
	/**
	 * update
	 * @param $data
	 * @throws InvalidArgumentException when parameters doesn't meet its contract
	 * 
	 * @return void
	 */
	public function update($data) {
		$this->order_lib->validate_update_data($data);
		
		// Sanitize user data
		$data['salesforce_order_id'] = $this->db->escape($data['salesforce_order_id']);
		$data['tracking_number'] = $this->db->escape($data['tracking_number']);
		$data['lot_code'] = $this->db->escape($data['lot_code']);
		$data['imei_number'] = $this->db->escape($data['imei_number']);
		$data['carrier'] = $this->db->escape($data['carrier']);
		$data['shipping_service'] = $this->db->escape($data['shipping_service']);
		
		// Prepare user sql
		$sql = $this->order_lib->update_sql($data);
		
		// execute the query
		$this->db->query($sql);
	}
	
	/**
	 * is_order_exists
	 * @param string $salesforce_order_id
	 * @throws InvalidArgumentException when $salesforce_id doesn't meet its contract
	 * 
	 * @return boolean $result
	 */
	public function is_order_exists($salesforce_order_id) {
		// Verify $salesforce_order_id
		$this->order_lib->validate_salesforce_order_id($salesforce_order_id);
		
		$salesforce_order_id = $this->db->escape($salesforce_order_id);
		
		// Prepare user sql
		$sql = $this->order_lib->check_order_exists_sql($salesforce_order_id);
		
		// execute the query
		$query = $this->db->query($sql);
		$result = $query->row();
		
		if ((int)$result->id > 0) {
			return true;
		}
		
		return false;
	}
}