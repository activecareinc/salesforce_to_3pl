<?php

use ThreePlCentral\ThreePlCentral;

/**
 * Class: ThreePl_Order_Model
 */
class ThreePl_Order_Model {
	
	/**
	 * 
	 * @var $three_pl
	 */
	protected $three_pl;
	
	/**
	 * default constructor
	 */
	public function __construct() {
		$this->three_pl = new ThreePlCentral(THREE_PL_KEY, CUSTOMER_ID, FACILITY_ID, USERNAME, PASSWORD);
	}
	
	/**
	 * create_order
	 * @param array $data
	 * 
	 * @return void
	 */
	public function create_order($data = array()) {
		// validate parameter
		if (strlen($data['order_ref_number']) < 1) {
			throw new InvalidArgumentException("Invalid order_ref_number passed. Must be string with value.");
		}
		
		if (strlen($data['customer']) < 1) {
			throw new InvalidArgumentException("Invalid customer passed. Must be string with value.");
		}
		
		if (strlen($data['ship_to_name']) < 1) {
			throw new InvalidArgumentException("Invalid ship_to_name passed. Must be string with value.");
		}
		
		$response = $this->three_pl->createOrder($this->three_pl, $data);
		
		return $response;
	}
	
	/**
	 * retrieve_order
	 * 
	 * @return void
	 */
	public function retrieve_order() {
		$response = $this->three_pl->findOrders((new DateTime())->modify('-30 days'), new DateTime());
		
		return $response;
	}
	
}