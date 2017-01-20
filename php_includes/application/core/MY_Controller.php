<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class: MY_Controller
 *
 * @see CI_Controller
 */
class MY_Controller extends CI_Controller {

	/**
	 * @var string $platform which the default platform is "desktop"
	 */
	public $platform;

	/**
	 * Redirection folder
	 *
	 * @var string $folder
	 */
	protected $folder;

	/**
	 * We will override the Constructor if we need anything on construct
	 *
	 * @return void
	 */
	public function __construct() {

		// we need to call the parent Constructor
		parent::__construct();

		// load libraries
		$this->load->library('API_Client_Transport', NULL, 'transport');
		$this->load->library('API_Client', NULL, 'client');

		// initialize client
		$this->client->init($this->transport, $this->session);

		// default platform
		$this->platform = 'desktop';

	}

	/**
	 * Always render the navigation bars
	 *
	 * @param string $view
	 * @param array $vars
	 * @param mixed $return
	 */
	public function render($view = '', $vars = array(), $return = FALSE) {
		// header variables with the navigation bars
		$header_vars = array();

		// flag for user authenticated
		$header_vars['authenticated'] = false;

		// header
		//$this->load->view($this->platform . '/common/header', $header_vars, $return);

		// contents
		$this->load->view($this->platform . '/'. $view, $vars, $return);

		// footer
		$footer_vars = array();

	//	$this->load->view($this->platform . '/common/footer', $footer_vars, $return);
	}
}