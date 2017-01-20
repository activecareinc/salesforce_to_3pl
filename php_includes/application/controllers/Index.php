<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
}