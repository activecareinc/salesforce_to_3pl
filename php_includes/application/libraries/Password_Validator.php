<?php

/**
 * Class: Password_Validator
 */
class Password_Validator {

	/**
	 * default constructor
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * @method is_valid
	 * Determines if the password is valid or not
	 *
	 * @param string $password
	 *
	 * @return boolean $result
	 */
	public function is_valid($password) {

		// initialize result to false
		$result = false;

		// password should have alpha and numeric characters
		// and should have minimum length of 8 characters
		// if password matches our valid condition
		// set result flag to true
		if (preg_match('/[a-z]/i', $password) === 1 && strlen(trim($password)) >= 8) {
			if (preg_match('/[0-9]/', $password)) {
				$result = true;
			}
		}

		// return result
		return $result;
	}
}