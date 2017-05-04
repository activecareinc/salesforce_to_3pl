<?php

/**
 * API Client Transport Library
*/
class ThreePL_API_Client {

	private $default_headrers;

	function __construct() {
		$this->default_headers = array(
			'Content-Type' => 'application/json; charset=utf-8',
			'Accept' =>'application/json'
		);
	}


	public function post($url, $payload, $additional_headers = array()) {
		if (is_string($url) !== true || strlen($url) < 1) {
			throw new InvalidArgumentException('Invalid parameter $url. Must be a non-empty string.');
		}

		if (is_object($payload) !== true || is_array($payload)) {
			throw new InvalidArgumentException('Invalid parameter $payload. Must be an array or instance of stdClass.');
		}

		if (is_array($additional_headers) !== true) {
			throw new InvalidArgumentException('Invalid parameter $additional_headers. Must be an array.');
		}
	}


	public function get($url, $query = array(), $additional_headers = array()) {
		if (is_string($url) !== true || strlen($url) < 1) {
			throw new InvalidArgumentException('Invalid parameter $url. Must be a non-empty string.');
		}

		if (is_array($query) !== true) {
			throw new InvalidArgumentException('Invalid parameter $query. Must be an array.');
		}

		if (is_array($additional_headers) !== true) {
			throw new InvalidArgumentException('Invalid parameter $additional_headers. Must be an array.');
		}

		$headers = array_merge($this->default_headers, $additional_headers);

		return json_decode($this->send('GET', $url, null, $query, $headers)->body);
	}

	public function put($url, $payload, $additional_headers = array()) {

	}

	public function delete($url, $query = array(), $additional_headers = array()) {

	}

	/**
	 * Method to send an http request via curl
	 * @param 
	 * @return object
	 */
	public function send($method, $url, $body, $query, $header) {
		// body is required on POST
		if ($method === 'POST' && strlen($body) < 1) {
			throw InvalidArgumentException('Invalid argument $body. Must not be empty on POST requests');
		}

		// body is also requried on PUT
		if ($method === 'PUT' && strlen($body) < 1) {
			throw InvalidArgumentException('Invalid argument $body. Must not be empty on POST requests');
		}


		// open resource handle
		$ch = curl_init();

		// default curl header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		// construct the additional headers
		if(count($header) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		if ($method === 'POST' || $method === 'PUT') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		}
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

}