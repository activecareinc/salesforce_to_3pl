<?php

require_once(__DIR__ . '/../../system/config.php');

require_once(__DIR__ . '/../../../php_includes/application/libraries/SalesForce/inclusions.php');

/**
 * verify the methods to communicate to SalesForce
 */
class SalesForce_Test extends PHPUnit_Framework_TestCase {


	/**
	 * default @setTup
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * verify when username is not given
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $username passed, cannot be empty
	 *
	 * @return void
	 */
	public function test_empty_username() {
		$client = new SforceEnterpriseClient();

		$salesForce = new SalesForce($client);
		$salesForce->authenticate('', '', '');
	}

	/**
	 * verify password is not given
	 * @depends test_empty_username
	 *
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $password passed, cannot be empty
	 *
	 * @return void
	 */
	public function test_empty_password() {
		$client = new SforceEnterpriseClient();

		$salesForce = new SalesForce($client);
		$salesForce->authenticate('username', '', '');
	}

	/**
	 * verify token is not given
	 * @depends test_empty_password
	 *
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter $token passed, cannot be empty
	 *
	 * @return void
	 */
	public function test_empty_token() {
		$client = new SforceEnterpriseClient();

		$salesForce = new SalesForce($client);
		$salesForce->authenticate('username', 'password', '');
	}

	/**
	 * verify the errors if we can't login
	 * @depends test_empty_username
	 * @depends test_empty_password
	 * given an invalid credentials
	 *
	 * @expectedException SoapFault
	 * @expectedException INVALID_LOGIN: Invalid username, password, security token; or user locked out.
	 *
	 * @return void
	 */
	public function test_invalid_credentials() {
		$client = new SforceEnterpriseClient();

		$salesForce = new SalesForce($client);
		$salesForce->authenticate('username', 'password', 'token');
	}

	/**
	 * verify we can login to SalesForce
	 * @depends test_invalid_credentials
	 * @return SalesForce $salesForce
	 */
	public function test_valid_credentials() {
		$client = new SforceEnterpriseClient();

		$salesForce = new SalesForce($client);
		$salesForce->authenticate(SALESFORCE_USERNAME, SALESFORCE_PASSWORD, SALESFORCE_TOKEN);

		// temporarily verify for now that these required fields are not empty
		// the next test will determine if this defined values are valid
		$this->assertTrue(strlen(trim($salesForce->getClient()->getSessionId())) > 0);

		return $salesForce;
	}

	/**
	 * verify we can make transactions to SalesForce
	 * 
	 * @depends test_valid_credentials
	 * @param SalesForce $salesForce
	 * @return void
	 */
	public function test_transaction(SalesForce $salesForce) {
		$order = $salesForce->getClient()->retrieve('Id, Name', 'Order', array('801Q00000002y0EIAQ'));

		// since the retrieve is always going to return an array of stdClass
		// we only wanted to get the first result
		$order = reset($order);

		// verify we have user returned
		$this->assertEquals($order->Id, '801Q00000002y0EIAQ');
	}
}