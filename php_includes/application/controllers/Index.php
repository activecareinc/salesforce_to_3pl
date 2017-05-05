<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(__DIR__ . '/../libraries/SalesForce/inclusions.php');

/**
 * Loads the homepage
 */
class Index extends MY_Controller {
	
	/**
	 * 3pl authentication token
	 */
	private $threepl_token;

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
		
		// set salesforce
		$this->salesforce_order_model->setSalesForce($salesforce);
		
		// load 3pl library then authenticate
		$this->load->library('ThreePL_API_Client', array(), 'threepl_lib');
		$this->threepl_token = $this->threepl_lib->authenticate(THREE_PL_KEY, FACILITY_ID)->access_token;
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
				$order_arr['ship_to_address'] = $order->ship_to_address;
				$order_arr['ship_to_city'] = $order->ship_to_city;
				$order_arr['ship_to_state'] = $order->ship_to_state;
				$order_arr['ship_to_postal_code'] = $order->ship_to_postal_code;
				$order_arr['ship_to_country'] = $order->ship_to_country;
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
				// prepare the data to create record to 3pl
				$threepl_order = array(
					'facility_identifier' => FACILITY_ID,
					'customer_identifier' => CUSTOMER_ID,
					'order_ref_number' => $order_to_3pl->$order_to_3pl->salesforce_order_id,
					'carrier' => strlen(trim($order_to_3pl->carrier)) > 1 ? $order_to_3pl->carrier : 'UPS',
					//'lot_code' =>  $order_to_3pl->lot_code,
					//'imei_number' => $order_to_3pl->imei_number,
					'service_level' => strlen(trim($order_to_3pl->service_level)) > 1 ? $order_to_3pl->service_level : 'Ground',
					'ship_to_company' => 'Bridge',
					'ship_to_name' => $order_to_3pl->ship_to_name,
					'ship_to_address' => $order_to_3pl->ship_to_address,
					'ship_to_city' => $order_to_3pl->ship_to_city,
					'ship_to_state' => $order_to_3pl->ship_to_state,
					'ship_to_postal_code' => $order_to_3pl->ship_to_postal_code,
					'ship_to_country' => strlen(trim($order_to_3pl->ship_to_country)) > 1 ? $order_to_3pl->ship_to_country : 'US',
				);
				
				$response = $this->threepl_lib->create_order($threepl_order, $this->threepl_token);
				$response_body = json_decode($response->body);
				
				// if the order is successfully created on 3pl
				// update the flag in the database once imported to 3pl
				if ($response_body->ReadOnly->OrderId > 1) {
					$this->order_model->update_is_import_to_3pl($order_to_3pl->salesforce_order_id, (int)$response_body->ReadOnly->OrderId);
				}
				
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
			$orders_from_3pl = $this->threepl_lib->retrieve_orders($this->threepl_token);
			$orders = $orders_from_3pl->ResourceList;
			
			// loop through the results from 3pl
			foreach ($orders as $order) { 
			
				// verify if we have tracking number for the order
				// else proceed to the next record on the loop
				if (strlen(trim($order->RoutingInfo->TrackingNumber)) > 0) {
					$order_data = array();
					$order_data['threepl_order_id'] = (int)$order->ReadOnly->OrderId;
					$order_data['tracking_number'] = $order->RoutingInfo->TrackingNumber;
					$this->order_model->update($order_data);
				} else {
					continue;
				}
				
			}
			
			// retrieve the records that will be imported to salesforce
			$salesforce_orders = $this->order_model->read_list_import_to_salesforce();
			
			// loop through the records
			foreach ($salesforce_orders as $salesforce_order) {
				$sf_order = new stdClass();
				$sf_order->Id = $salesforce_order->salesforce_order_id;
				$sf_order->Tracking_Number__c = $salesforce_order->tracking_number;
				
				// update the record in salesforce
				$update = $this->salesforce_order_model->update($sf_order);
				
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