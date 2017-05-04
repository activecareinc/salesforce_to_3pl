<?php

/**
 * API Client Transport Library
*/
class ThreePL_API_Client {

	private $default_headers;

	private $base_url = 'http://secure-wms.com';

	function __construct() {
		$this->default_headers = array(
			'Content-Type: application/json; charset=utf-8',
			'Accept: application/json'
		);
	}


	/**
	 * Send a POST request to the 3pl API
	 * @param string $url
	 * @param object|array $payload An object or arry containing the data to be sent. Will be implicitly converted to a json string before being sent.
	 * @param array $additional_headers array of additional headers to include in the request
	 * @return object wraps the response headers, body and the original request sent to the api
	 */
	public function post($url, $payload, $additional_headers = array()) {
		// validate url
		if (is_string($url) !== true || strlen($url) < 1) {
			throw new InvalidArgumentException('Invalid parameter $url. Must be a non-empty string.');
		}

		// check if payload is valid
		if (is_object($payload) !== true && is_array($payload) !== true) {
			throw new InvalidArgumentException('Invalid parameter $payload. Must be an array or instance of stdClass.');
		}

		// check if additional headers is an array
		// no need to check for length since additional headers are optional
		if (is_array($additional_headers) !== true) {
			throw new InvalidArgumentException('Invalid parameter $additional_headers. Must be an array.');
		}

		// merge additional headers with default
		$headers = array_merge($this->default_headers, $additional_headers);
		return $this->send('POST', $url, json_encode($payload), array(), $headers);
	}


	/**
	 * Send a GET request to the 3pl API
	 * @param string $url
	 * @param array $query a key-value array containing the data for the query string
	 * @param array $additional_headers array of additional headers to include in the request
	 * @return object wraps the response headers, body and the original request sent to the api
	 */
	public function get($url, $query = array(), $additional_headers = array()) {
		// validate url
		if (is_string($url) !== true || strlen($url) < 1) {
			throw new InvalidArgumentException('Invalid parameter $url. Must be a non-empty string.');
		}

		// validate query
		// do not check for length because query is optional
		if (is_array($query) !== true) {
			throw new InvalidArgumentException('Invalid parameter $query. Must be an array.');
		}


		// check if additional headers is an array
		// no need to check for length since additional headers are optional
		if (is_array($additional_headers) !== true) {
			throw new InvalidArgumentException('Invalid parameter $additional_headers. Must be an array.');
		}

		// merge headers
		$headers = array_merge($this->default_headers, $additional_headers);

		return $this->send('GET', $url, null, $query, $headers);
	}

	/**
	 * Method to send an http request via curl
	 * @param string $method 
	 * @param string $url
	 * @param string $body
	 * @param array $query
	 * @param array $header
	 * @return object
	 */
	public function send($method, $url, $body, $query, $header) {
		// body is required on POST
		if ($method === 'POST' && strlen($body) < 1) {
			throw new InvalidArgumentException('Invalid parameter $body. Must not be empty on POST requests');
		}


		// open resource handle
		$ch = curl_init();

		// default curl header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);



		if ($method === 'POST' || $method === 'PUT') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		}

		// construct the additional headers
		if(count($header) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		$url = $this->base_url . $url;
		// construct the data only if it is not empty
		$url .= '?' . http_build_query($query);

		curl_setopt($ch, CURLOPT_URL, $url);

		// header info
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		$request = new stdClass;

		$response = new stdClass();


		// execute curl request
		$curl_response = curl_exec($ch);

		$response->header = curl_getinfo ($ch);
		$response->body = $curl_response;

		$request->header = $response->header['request_header'];
		$request->body = $body;
		$response->original_request = $request;


		// handle when there is an error
		if(strlen(curl_error($ch)) > 0) {
			throw new RuntimeException(curl_error($ch));
		}


		// close connection
		curl_close($ch);

		return $response;
	}

	/**
	 * Send an authentication request to 3pl
	 * @param string $key
	 * @param string $login_id
	 * @return object plain object containing access_token
	 */
	public function authenticate($key, $login_id) {

		// check if key is a valid string
		if (is_string($key) !== true || strlen($key) < 1) {
			throw new InvalidArgumentException('Invalid parameter $key. Must be a non-empty string.');
		}

		// check if login is a valid string
		if (is_string($login_id) !== true || strlen($login_id) < 1) {
			throw new InvalidArgumentException('Invalid parameter $login_id. Must be a non-empty string.');
		}
		// set headers for authentication
		$headers = array(
			'Authorization: Basic ZmE2OWNiM2QtMGYyMy00Y2JjLThlZWEtMzA5YTVkOWU3ZDRmOldUbVNzY0MzRFpQK1M2VkVWejVLT3BjLzVxYkRQYWdr'
		);

		// create object for the request's body
		$body = new stdClass;
		$body->grant_type = 'client_credentials';
		$body->tpl = $key;
		$body->user_login_id = $login_id;

		// send post request
		$response = $this->post('/AuthServer/api/Token', $body, $headers);

		return json_decode($response->body);
	}


	/**
	 * Send a request to the api to create an order
	 * @param array $data
	 * @param string $token
	 * @return 
	 */	
	public function create_order($data, $token) {

		// validate $data
		if (is_array($data) !== true || count($data) < 1) {
			throw new InvalidArgumentException('Invalid parameter $data. Must be an non-empty array');
		}

		// check for api token
		if (is_string($token) !== true || strlen($token) < 1) {
			throw new InvalidArgumentException('Invalid parameter $token. Must be an non-empty string');
		}

		// check that customer id is provided
		if (isset($data['customer_identifier']) !== true || is_string($data['customer_identifier']) !== true || strlen($data['customer_identifier']) < 1) {
			throw new InvalidArgumentException("Invalid parameter 'customer_identifier'. Must be an non-empty string ");
		}

		// validate facility id
		if (isset($data['facility_identifier']) !== true || is_string($data['facility_identifier']) !== true || strlen($data['facility_identifier']) < 1) {
			throw new InvalidArgumentException("Invalid parameter 'facility_identifier'. Must be an non-empty string");
		}

		// validate reference number
		if (isset($data['order_ref_number']) !== true || is_string($data['order_ref_number']) !== true) {
			throw new InvalidArgumentException("Invalid parameter 'order_ref_number'. Must be an non-empty string");
		}

		// validate carrier
		if (isset($data['carrier']) !== true || is_string($data['carrier']) !== true) {
			throw new InvalidArgumentException("Invalid parameter 'carrier'. Must be an non-empty string");
		}

		// validate service level
		if (isset($data['service_level']) !== true || is_string($data['service_level']) !== true) {
			throw new InvalidArgumentException("Invalid parameter 'service_level'. Must be an non-empty string");
		}

		// validate shipping data
		if (isset($data['ship_to_name']) !== true || is_string($data['ship_to_name']) !== true) {
			throw new InvalidArgumentException("Invalid parameter 'ship_to_name'. Must be an non-empty string");
		}
		if (isset($data['ship_to_company']) !== true || is_string($data['ship_to_company']) !== true) {
			throw new InvalidArgumentException("Invalid parameter 'ship_to_company'. Must be an non-empty string");
		}
		if (isset($data['ship_to_address']) !== true || is_string($data['ship_to_address']) !== true) {
			throw new InvalidArgumentException("Invalid parameter 'ship_to_address'. Must be an non-empty string");
		}
		if (isset($data['ship_to_city']) !== true || is_string($data['ship_to_city']) !== true) {
			throw new InvalidArgumentException("Invalid parameter 'ship_to_city'. Must be an non-empty string");
		}
		if (isset($data['ship_to_state']) !== true || is_string($data['ship_to_state']) !== true) {
			throw new InvalidArgumentException("Invalid parameter 'ship_to_state'. Must be an non-empty string");
		}
		if (isset($data['ship_to_postal_code']) !== true || is_string($data['ship_to_postal_code']) !== true) {
			throw new InvalidArgumentException("Invalid parameter 'ship_to_postal_code'. Must be an non-empty string");
		}
		if (isset($data['ship_to_country']) !== true || is_string($data['ship_to_country']) !== true) {
			throw new InvalidArgumentException("Invalid parameter 'ship_to_country'. Must be an non-empty string");
		}

		// create new object to hold order data
		$order = new stdClass;

		// set customer id
		$order->customerIdentifier = new stdClass;
		$order->customerIdentifier->id = $data['customer_identifier'];
		// set facility id
		$order->facilityIdentifier = new stdClass;
		$order->facilityIdentifier->id = $data['facility_identifier'];
		// set order ref number
		$order->referenceNum = $data['order_ref_number'];
		$order->routingInfo = new stdClass;
		$order->routingInfo->carrier = $data['carrier'];
		$order->routingInfo->mode = $data['service_level'];
		// set shipping data
		$order->shipTo = new stdClass;
		$order->shipTo->companyName = $data['ship_to_company'];
		$order->shipTo->name = $data['ship_to_name'];
		$order->shipTo->address1 = $data['ship_to_address'];
		$order->shipTo->city = $data['ship_to_city'];
		$order->shipTo->state = $data['ship_to_state'];
		$order->shipTo->zip = $data['ship_to_postal_code'];
		$order->shipTo->country = $data['ship_to_country'];

		// add token to the header
		$headers = array(
			'Authorization: Bearer ' . $token
		);

		// send request
		$response = $this->post('/orders', $order, $headers);
		// check the http status
		$response_code = $response->header['http_code'];
		// if not 201 Created
		if ($response->header['http_code'] !== 201) {
			// log response
			error_log(json_encode($response));
			$body = json_decode($response->body);
			throw new RuntimeException($body->ErrorCode);
		}
		return $response;
	}

}