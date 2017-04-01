<?php

/**
 * Soap Wrapper for SforceEnterpriseClient
 */
class SalesForce {
	
	/**
 	 * @var SforceEnterpriseClient $client
	 */
	private $client;
	
	/**
	 * default constructor
	 */
	public function __construct(SforceEnterpriseClient $client) {
		$this->client = $client;
	}

	/**
	 * @return SforceEnterpriseClient $client
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * authenticate
	 * authorization required when using Salesforce
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $token
	 *
	 * @return void
	 */
	public function authenticate($username, $password) {
		$result = false;

		// Check the required parameters
		if(strlen(trim($username)) < 1) {
			throw new InvalidArgumentException('Invalid parameter $username passed, cannot be empty');
		}

		if(strlen(trim($password)) < 1) {
			throw new InvalidArgumentException('Invalid parameter $password passed, cannot be empty');
		}

		// set this explicitly to NULL when
		// no organizationId is set to the Application
		// we are connecting to
		$this->client->setLoginScopeHeader(null);
			
		// create the connection, then login user
		$this->client->createConnection(__DIR__ . '/soapclient/wsdl.jsp.xml');
		$this->client->login( $username, $password);

		if(strlen(trim($this->client->getSessionId())) < 1) {
			throw new RuntimeException('Unable to authenticate. Invalid account credentials');
		}
	}
	
}
