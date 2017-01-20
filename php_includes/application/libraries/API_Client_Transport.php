<?php

/**
 * API Client Transport Library
 */
class API_Client_Transport {
	/**
	 * default constructor
	 * @returrn void
	 */
	public function __construct() {

	}

	/**
	 * execute send request
	 * @param string $endpoint
	 * @param array $data
	 * @param string $request (default to POST)
	 * @param array $header (optional)
	 *
	 * @throws InvalidArgumetnException when $endpoint is not valid
	 * @throws InvalidArgumentException when $data is not array
	 * @throws InvalidArgumentException when $request is not either POST or GET
	 * @throws InvalidArgument when $header not array
	 * @throws RuntimeException when curl returns an error
	 *
	 * @return stdClass $result
	 */
	public function send($endpoint, $data, $request='POST', $header = array()) {
		// verify arguments validity
		if(is_string($endpoint) !== true || strlen($endpoint) < 1) {
			throw new InvalidArgumentException('Invalid parameter "endpoint" passed. Must be a valid endpoint.');
		}

		// verify arguments validity
		if(is_array($data) !== true) {
			throw new InvalidArgumentException('Invalid parameter "data" passed. Must be a valid array.');
		}

		// verify arguments validity
		if(is_string($request) !== true || in_array($request, array('POST', 'GET')) !== true) {
			throw new InvalidArgumentException('Invalid parameter "request" passed. Must only be either POST or GET.');
		}

		// verify arguments validity
		if(is_array($header) !== true) {
			throw new InvalidArgumentException('Invalid parameter "header" passed. Must be a valid array.');
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

		// URL to curl
		$url = API_URL . $endpoint;

		// construct the data only if it is not empty
		if(count($data) > 0) {
			if($request == 'POST') {
				// added http_build_query since it cannot
				// pass correctly multidimensional array
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			} else {
				// append to query string
				$url .= '?' . http_build_query($data);
			}
		}

		curl_setopt($ch, CURLOPT_URL, $url);

		// header info
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);

		// execute curl request
		$response = curl_exec($ch);

		// handle when there is an error
		if(strlen(curl_error($ch)) > 0) {
			throw new RuntimeException(curl_error($ch));
		}

		// close connection
		curl_close($ch);

		// expects JSON encoded string
		$result = json_decode($response);

		return $result;
	}
}