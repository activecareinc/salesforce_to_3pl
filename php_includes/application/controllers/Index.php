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
		$salesforce->authenticate(SALESFORCE_USERNAME, SALESFORCE_PASSWORD);
		
		// load required libraries
		$this->load->library('SalesForce_Order_Library', array(), 'salesforce_order_lib');
		$this->load->library('Order_Library', array(), 'order_lib');
		$this->load->model('SalesForce_Order_Model', 'salesforce_order_model');
		$this->load->model('Order_Model', 'order_model');
		
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
				$order_arr['customer'] = $order->customer;
				
				// check if the order record already exist in database
				$is_exist = $this->order_model->is_order_exists($order->salesforce_order_id);
				error_log($is_exist);
				if ($is_exist === false) {
					// insert into table
					$this->order_model->insert($order_arr);
				}
			}
			
			// retrieve the records from the database that will be imported to 3pl
			$orders_to_3pl = $this->order_model->read_list_not_import_to();
			
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
			
			// retrieve the records that will be imported to salesforce
			$orders = $this->order_model->read_list_import_to_salesforce();
			
			// loop through the records
			foreach ($orders as $order) {
				$salesforce_order = new stdClass();
				$salesforce_order->Id = $order->salesforce_order_id;
				$salesforce_order->Description = $order->tracking_number;
				
				// update the record in salesforce
				$this->salesforce_order_model->update($salesforce_order);
			}
			
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
		
	}
}