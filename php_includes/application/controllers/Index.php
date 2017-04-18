<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(__DIR__ . '/../libraries/SalesForce/inclusions.php');

/**
 * Loads the homepage
 */
class Index extends MY_Controller {

	/**
	 * constructor
	 */
	public function __construct()
	{
		// Construct our parent class
		parent::__construct();
		
		// authenticate to salesforce
		$client = new SforceEnterpriseClient();
		$salesforce = new SalesForce($client);
		$salesforce->authenticate(SALESFORCE_USERNAME, SALESFORCE_PASSWORD, SALESFORCE_TOKEN);
		
		// load required libraries
		$this->load->library('SalesForce_Order_Library', array(), 'salesforce_order_lib');
		$this->load->library('Order_Library', array(), 'order_lib');
		$this->load->model('SalesForce_Order_Model', 'salesforce_order_model');
		$this->load->model('Order_Model', 'order_model');
		$this->load->model('ThreePl_Order_Model', array(THREE_PL_KEY, CUSTOMER_ID, FACILITY_ID, USERNAME, PASSWORD), 'threepl_order_model');
		
		// set salesforce
		$this->salesforce_order_model->setSalesForce($salesforce);
	}

	/**
	 * Loads the homepage
	 *
	 * @return void
	 */
	public function index()
	{
		// load login page
		$this->render('welcome_message');
	}
	
	/**
	 * method to fetch records from salesforce
	 * to database to agile
	 * 
	 * @return void
	 */
	public function salesforce_to_agile() {
		try {
			
			// retrieve the active order records for 'activecare' account
			$orders = $this->salesforce_order_model->read();
			
			// loop through the orders and insert records to db
			foreach ($orders as $order) {
				
				// initialize array
				$order_arr = array();
				$order_arr['salesforce_order_id'] = $order->salesforce_order_id;
				$order_arr['tracking_number'] = $order->tracking_number;
				$order_arr['order_expiration_date'] = $order->order_expiration_date;
				$order_arr['lot_code'] =  $product->lot_code;
				$order_arr['imei_number'] = $product->imei_number;
				$order_arr['carrier'] = $order->carrier;
				$order_arr['shipping_service'] = $order->shipping_service;
				$order_arr['customer'] = $order->customer;
				$order_arr['order_ref_no'] = $order->order_ref_no;
				$order_arr['ship_to_name'] = $order->ship_to_name;
				$order_arr['order_date_created'] = $order->order_date_created;
				
				// check if the order record already exist in database
				$is_exist = $this->order_model->is_order_exists($order->salesforce_order_id);
				
				if ($is_exist === false) {
					// insert into table
					$this->order_model->insert($order_arr);
				}
			}
			
			// retrieve the records from the database that will be imported to 3pl
			$orders_to_3pl = $this->order_model->read_list_not_import_to();
			
			// loop through the orders saved in the database
			// create the orders in 3PL
			foreach ($orders_to_3pl as $order_to_3pl) {
				$threepl_order = array();
				$threepl_order['order_ref_number'] = $order_to_3pl->order_ref_no;
				$threepl_order['customer'] = $order_to_3pl->customer;
				$threepl_order['ship_to_name'] = $order_to_3pl->ship_to_name;
			
				$order_create = $this->threepl_order_model->create_order();
				
				// update the flag in the database once imported to 3pl
				// @todo need to check how the salesforce id is saved in 3pl
				// and also response from 3pl once order is successfully created
				$this->order_model->update_is_import_to_3pl();
				
			}
			
		} catch (Exception $e) {
			error_log($e->getMessage() . $e->getLine() . $e->getFile());
		}
	}
	
	/**
	 * method to update record to salesforce from agile
	 * 
	 * @return void
	 */
	public function agile_to_salesforce() {
		try {
			
			// retrieve the orders from 3pl
			$orders_from_3pl = $this->threepl_order_model->retrieve_order();
			
			// update the details stored in database
			// @todo add other details that will be saved to database
			// and check response from 3pl
			foreach ($orders_from_3pl as $order_from_3pl) {
				$order_data = array();
				$order_data['tracking_number'] = $order_from_3pl->TrackingNumber;
				$this->order_model->update($order_data);
			}
			
			// retrieve the records that will be imported to salesforce
			$orders = $this->order_model->read_list_import_to_salesforce();
			
			// loop through the records
			foreach ($orders as $order) {
				$salesforce_order = new stdClass();
				$salesforce_order->Id = $order->salesforce_order_id;
				
				// @todo change this one to add other details on the description field
				$salesforce_order->Tracking_Number__c = $order->tracking_number;
				
				// update the record in salesforce
				$update = $this->salesforce_order_model->update($salesforce_order);
				
				// verify if the record in salesforce is updated
				if ($update[0]->success === true) {
					// update the record in db
					$this->order_model->update_is_salesforce_updated($update[0]->id);
				}
				
			}
			
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
		
	}
}