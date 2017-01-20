<?php

/**
 * API Client Library
 */
class API_Client {
	/**
	 * API transport
	 *
	 * @var API_Client_Transport
	 */
	protected $transport;

	/**
	 * Session class
	 *
	 * @var CI_Session
	 */
	protected $session;

	/**
	 * Request attempt
	 *
	 * @var int
	 */
	protected $attempt = 0;

	/**
	 * Initialize transport
	 *
	 * @param API_Client_Transport $transport
	 */
	public function init(API_Client_Transport $transport, $session) {
		// Set transport
		$this->transport = $transport;

		// Set session class
		$this->session = $session;
	}

	/**
	 * Generate a string with random pseudo
	 * bytes with 9 bytes and convert to hex.
	 *
	 * @return string $state
	 */
	public function generate_state() {
		$bytes = openssl_random_pseudo_bytes(9, $cstrong);
		return bin2hex($bytes);
	}

	/**
	 * generate random crypto 24 bytes string
	 * @see http://php.net/manual/en/function.openssl-random-pseudo-bytes.php
	 * @return string $hash
	 */
	public function generate_hash() {
		// strong cryptographic string
		$strong = TRUE;
		$bytes =  openssl_random_pseudo_bytes(24, $strong);
		$hash = bin2hex($bytes);

		return $hash;
	}

	/**
	 * Construct the header and post data
	 * Execute API_Client::generate_state()
	 * Execute method $this->transport->send(login, $data)
	 *
	 * @param string $email
	 * @param string $password
	 * @throws InvalidArgumentException
	 * @return stdClass $result
	 */
	public function authenticate($email, $password) {
		// Initialize data
		$data = compact('email', 'password');

		// Verify meets contract
		foreach ($data as $key => $value) {
			if (strlen(trim($value)) < 1) {
				throw new InvalidArgumentException("Invalid parameter \${$key}, must be a valid string.");
			}
		}

		// Verify email contract
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			throw new InvalidArgumentException('Invalid parameter $email, must be a valid email.');
		}

		// Set grant type to password
		$data['redirect_uri'] = BASE_URL;
		$data['state'] = $this->generate_state();
		$data['login_type'] = WEBSITE;

		// Set token header
		$header = array(
			'X-Client-ID: ' . CLIENT_ID,
			'X-Client-Key: ' . CLIENT_SECRET
		);

		// Get result
		$result = $this->transport->send('/accounts/login', $data, 'POST', $header);

		// verify if the user has already granted the application
		// if so, return the access token
		// or otherwise show the grant page
		if (
			isset($result->data->code) === true &&
			strlen(trim($result->data->code)) > 0 &&
			$data['state'] == $result->data->state
		) {

			// redirect to grant page
			// pass the grant code and redirect URI

		} elseif (
			isset($result->data->access_token) !== true ||
			isset($result->data->refresh_token) !== true ||
			isset($result->data->expire_on) !== true ||
			$result->data->expire_on < time()
		) {
			// unable to authenticate
			// do nothing, error message is being passed on the response
		}

		// Token session
		$this->session->set_userdata('token', $result);

		return $result;
	}

	/**
	 * Construct the header and post data with grant_type authorization_code
	 * Execute method $this->transport->send(authorize, $data)
	 *
	 * @param string $code
	 * @param string $state
	 * @return stdClass $result
	 */
	public function authorize($code, $state) {
		// Initialize data
		$data = array('auth_code' => $code, 'state' => $state);

		// Verify meets contract
		foreach ($data as $key => $value) {
			if (strlen(trim($value)) < 1) {
				throw new InvalidArgumentException("Invalid parameter \${$key}, must be a valid string.");
			}
		}

		// Set grant type to authorization_code
		$data['grant_type'] = 'authorization_code';
		$data['redirect_uri'] = BASE_URL;

		// Set token header
		$header = array(
			'X-Client-ID: ' . CLIENT_ID,
			'X-Client-Key: ' . CLIENT_SECRET
		);

		// Get result
		$result = $this->transport->send('/accounts/authorize', $data, 'POST', $header);

		// Verify result code
		if (isset($result->access_token) !== true || strlen(trim($result->access_token)) < 1) {
			throw new RuntimeException('Invalid access token returned.');
		}

		// Verify refresh_token
		if (isset($result->refresh_token) !== true || strlen(trim($result->refresh_token)) < 1) {
			throw new RuntimeException('Invalid refresh token returned.');
		}

		// Verify state
		if (isset($result->expires_on) !== true || ((int) $result->expires_on <= time())) {
			throw new RuntimeException('Invalid refresh token, already expired.');
		}

		// Token session
		$token = new stdClass();
		$token->access_token = $result->access_token;
		$token->refresh_token = $result->refresh_token;

		// Set token session
		$this->session->set_userdata('token', $token);
	}

	/**
	 * Construct the header and post data with grant_type refresh_token
	 * Execute method $this->transport->send(token, $data)
	 *
	 * @param string $refresh_token
	 * @return stdClass $result
	 */
	public function refresh_token($refresh_token) {

		// Verify contract
		if (strlen(trim($refresh_token)) < 1) {
			throw new InvalidArgumentException('Invalid parameter $refresh_token, must be a valid string.');
		}

		// Initialize data
		$data = array('grant_type' => 'refresh_token', 'refresh_token' => $refresh_token);

		// Set token header
		$header = array(
			'X-Client-ID: ' . CLIENT_ID,
			'X-Client-Key: ' . CLIENT_SECRET
		);

		// Get result
		$result =  $this->transport->send('token', $data, 'POST', $header);

		// Verify result code
		if (isset($result->access_token) !== true || strlen(trim($result->access_token)) < 1) {
			throw new RuntimeException('Invalid access token returned.');
		}

		// Verify refresh_token
		if (isset($result->refresh_token) !== true || strlen(trim($result->refresh_token)) < 1) {
			throw new RuntimeException('Invalid refresh token returned.');
		}

		// Verify state
		if (isset($result->expires_on) !== true || ((int) $result->expires_on <= time())) {
			throw new RuntimeException('Invalid refresh token, already expired.');
		}

		// Token session
		$token = new stdClass();
		$token->access_token = $result->access_token;
		$token->refresh_token = $result->refresh_token;

		// Set token session
		$this->session->set_userdata('token', $token);
	}

	/**
	 * Execute method $this->transport->send($endpoint, $data)
	 *
	 * @param string $endpoint
	 * @param array $data
	 * @param stdClass $token is optional for accessing public endpoints
	 * @return stdClass $result
	 */
	public function request($endpoint, $data, $token='', $request = 'POST', $additional_headers = array()) {
		// Verify contract
		if (strlen(trim($endpoint)) < 1) {
			throw new InvalidArgumentException('Invalid parameter $endpoint, must be a valid string.');
		}

		// Verify contract
		if (is_array($data) !== true || count($data) < 1) {
			throw new InvalidArgumentException('Invalid parameter $data, must be an array with values.');
		}

		// initialize header
		$header = array();

		// token is optional, so when it is empty
		// make sure to use the API key/secret to connect to server
		// force empty() because the value might be a string or an object
		if(empty($token) !== true) {
			if (is_a($token, 'stdClass') !== true) {
				throw new InvalidArgumentException('Invalid parameter $token, must be a valid token object.');
			}

			$header[] = 'Authorization: Access Token ' . $token->data->access_token;
		} else {
			$header[] = 'X-Client-ID: ' . CLIENT_ID;
			$header[] = 'X-Client-Key: ' . CLIENT_SECRET;
		}

		// Increment attempt
		// ideally, the attempt counter is initially set to 0
		// incrementing this the first time will set the value to 1
		++$this->attempt;

		if(count($additional_headers) > 0) {
			foreach ($additional_headers AS $additional_header) {
				$header[] = $additional_header;
			}
		}
		$result = $this->transport->send($endpoint, $data, $request, $header);

		// Attempt to refresh the token if status is 501
		if ($this->attempt === 1 && $result->status === API_STATUS_CODE_REFRESH_TOKEN) {

			// Attempt refresh token
			$this->refresh_token($token->data->refresh_token);

			// Increment attempt
			++$this->attempt;

			// Get session token
			$token = $this->session->userdata('token');

			// Get result
			$result = $this->request($endpoint, $data, $token, $request, $header);

			// unset session request error
			$this->session->unset_userdata('request_error');
		}

		// Check error and status
		if (empty($result->error) !== true || (isset($result->status) && (int) $result->status !== API_STATUS_CODE_OK)) {
			// if the user has an active session
			// this could tell that the session might have been revoked
			// or it already expired
			// but only redirect if the user has already attempted to refresh the token
			$session = $this->session->userdata('token');
			$this->session->set_userdata('request_error', $result->error);

			if(isset($session->data->access_token) === true && ($this->attempt > 1 || $result->status == API_STATUS_CODE_UNAUTHORIZED)) {
				// boot the user out
				$namespace = '';

				// parse the namespace from the uri string
				$uri_string = uri_string();
				$query_string = explode('/', $uri_string);

				if(isset($query_string[0]) === true && strtolower($query_string[0]) === 'admin') {
					$namespace = 'admin';
				}

				redirect($namespace . '/login/logout');
			} else {
				throw new RuntimeException('There was an error getting the result.');
			}
		}

		// Re-initialize attempt to 0
		$this->attempt = 0;

		return $result;
	}
}