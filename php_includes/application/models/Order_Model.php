<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Order_Model extends CI_Model {
	
	
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
		$data['ship_to_company'] = $this->db->escape($data['ship_to_company']);
		$data['ship_to_address'] = $this->db->escape($data['ship_to_address']);
		$data['ship_to_city'] = $this->db->escape($data['ship_to_city']);
		$data['ship_to_state'] = $this->db->escape($data['ship_to_state']);
		$data['ship_to_postal_code'] = $this->db->escape($data['ship_to_postal_code']);
		$data['ship_to_country'] = $this->db->escape($data['ship_to_country']);
		
		// Prepare user sql
		$sql = $this->order_lib->insert_sql($data);
		
		// execute the query
		$query = $this->db->query($sql);
		
		// verify there was no error in the query
		if ($query !== true) {
			throw new RuntimeException("Error inserting orders.");
		}
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
	
	/**
	 * update_is_import_to_3pl
	 * @param string $salesforce_order_id
	 * 
	 * @return void
	 */
	public function update_is_import_to_3pl($salesforce_order_id, $order_id) {
		// Verify $salesforce_order_id
		$this->order_lib->validate_salesforce_order_id($salesforce_order_id);
		
		$salesforce_order_id = $this->db->escape($salesforce_order_id);
		
		// Prepare user sql
		$sql = $this->order_lib->update_is_import_to_3pl_sql($salesforce_order_id, $order_id);
		
		// execute the query
		$this->db->query($sql);
	}
	
	/**
	 * read_list_not_import_to
	 * @throws RuntimeException when there is an error in executing the query
	 * 
	 * @return array $result
	 */
	public function read_list_not_import_to() {
		// Prepare user sql
		$sql = $this->order_lib->read_list_not_import_to_3pl();
		
		// execute the query
		$query = $this->db->query($sql);
		
		// Verify there was no error in the query
		if (is_object($query)!== true) {
			throw new RuntimeException('Error retrieving records.');
		}
		
		// Get results
		$result = $query->result();
		
		return $result;
	}
	
	/**
	 * read_list_import_to_salesforce
	 * @throws RuntimeException when there is an error in executing the query
	 * 
	 * @return array $result
	 */
	public function read_list_import_to_salesforce() {
		// Prepare user sql
		$sql = $this->order_lib->read_list_import_to_salesforce();
		
		// execute the query
		$query = $this->db->query($sql);
		
		// Verify there was no error in the query
		if (is_object($query) !== true) {
			throw new RuntimeException('Error retrieving records.');
		}
		
		// Get results
		$result = $query->result();
		
		return $result;
	}
	
	/**
	 * update_is_salesforce_updated
	 * @param string $salesforce_order_id
	 * 
	 * @return void
	 */
	public function update_is_salesforce_updated($salesforce_order_id) {
		// Verify $salesforce_order_id
		$this->order_lib->validate_salesforce_order_id($salesforce_order_id);
		
		$salesforce_order_id = $this->db->escape($salesforce_order_id);
		
		// Prepare user sql
		$sql = $this->order_lib->update_is_salesforce_updated_sql($salesforce_order_id);
		
		// execute the query
		$this->db->query($sql);
	}
}