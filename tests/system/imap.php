<?php

	/**
	 * IMAP
	 * @see http://www.php.net/manual/en/book.imap.php
	 */
	class IMAP {
		/**
		 * @var stdClass $mbox
		 */
		private $mbox;

		/**
		 * constructor
		 */
		public function __construct() {

		}

		/**
		 * connect
		 * connect to imap server
		 * @see http://www.php.net/manual/en/function.imap-open.php
		 * @param string $imapserver
		 * @param string $username
		 * @param string $password
		 * @param string $port
		 * @param string $option
		 * @throws InvalidArgumentException if $imapserver is not a string or length < 1
		 * @throws InvalidArgumentException if $username is not a string or length < 1
		 * @throws InvalidArgumentException if $password is not a string or length < 1
		 * @throws InvalidArgumentException if $port is not a string or length < 1
		 * @throws RuntimeException if invalid credentials imap_open(): Couldn\'t open stream {$imapserver:$port/imap/ssl}INBOX
		 * @return boolean $result true on success or false on failure
		 */
		public function connect($imapserver = '', $username = '', $password = '', $port = '') {
			// verify parameter meets the expected contract

			if(is_string($imapserver) !== true || strlen(trim($imapserver)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "imapserver" passed, must be a string and length > 0');
			}

			// verify parameter meets the expected contract
			if(is_string($username) !== true || strlen(trim($username)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "username" passed, must be a string and length > 0');
			}

			// verify parameter meets the expected contract
			if(is_string($password) !== true || strlen(trim($password)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "password" passed, must be a string and length > 0');
			}

			// verify parameter meets the expected contract
			if(is_string($port) !== true || strlen(trim($port)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "port" passed, must be a string and length > 0');
			}

			// instantiate the imap object
			$this->mbox = imap_open('{'.$imapserver.':'.$port.'/imap/ssl/novalidate-cert}INBOX', $username, $password, CL_EXPUNGE);

			// verify that it was able to connect to the imap server
			if($this->mbox === false) {
				throw new RuntimeException('imap_open(): Couldn\'t open stream {'.$imapserver.':'.$port.'/imap/ssl}INBOX');
				$result = false;
			} else {
				$result = true;
			}

			return $result;

		}

		/**
		 * close
		 * close a IMAP connection
		 * @see http://www.php.net/manual/en/function.imap-close.php
		 * @throws RuntimeException if imap server was not connected
		 * @throws RuntimeException if unable to close the connection
		 * @return boolean $result, TRUE on success or FALSE on failure.
		 */
		public function close() {

			// verify that the mailbox stream is connected
			if($this->mbox === false) {
				throw new RuntimeException('imap server was not connected.');
			}

			// initialize close the connection to the imap server
			$result = imap_close($this->mbox);

			// verify if the close of connection is successful
			if($result === false) {
				throw new RuntimeException('Unable to close the connection.');
			}

			// return the close connection
			return $result;

		}

		/**
		 * search
		 * Returns an array of messages matching the given search criteria
		 * @see http://www.php.net/manual/en/function.imap-headers.php
		 * @param string $search
		 * @param string $criteria, for criteria format check the http://www.php.net/manual/en/function.imap-search.php
		 * @param string $options, for options format check the http://www.php.net/manual/en/function.imap-search.php
		 * @throws RuntimeException if imap server was not connected
		 * @throws InvalidArgumentException if $criteria, Invalid parameter "criteria" passed, must be a string and length > 0
		 * @return array $result, Returns an array of message numbers or UIDs.
		 */
		public function search($search='', $criteria='ALL') {
			// verify that the mailbox stream is connected
			if($this->mbox === false) {
				throw new RuntimeException('imap server was not connected.');
			}

			// verify parameter meets the expected contract
			if(is_string($criteria) !== true || strlen(trim($criteria)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "criteria" passed, must be a string and length > 0');
			}

			// verify the search criteria syntax
			if ($criteria == 'ALL')
				$search_criteria = 'ALL';
			else
				$search_criteria = $criteria.' "'.$search.'"';


			// execute the email search based on criteria
			$result = imap_search($this->mbox, $search_criteria);

			// return the list of searchresults
			return $result;
		}

		/**
		 * delete
		 * delete a message from current mailbox
		 * @see http://www.php.net/manual/en/function.imap-delete.php
		 * @param string $messagenumber
		 * @throws RuntimeException if imap server was not connected
		 * @throws InvalidArgumentException if $messagenumber is not a string or length < 1
		 * @return boolean $result true on success; false on failure
		 */

		public function delete($messagenumber = ''){
			// verify that the mailbox stream is connected
			if($this->mbox === false) {
				throw new RuntimeException('imap server was not connected.');
			}

			// verify parameter meets the expected contract
			if(is_string($messagenumber) !== true || strlen(trim($messagenumber)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "messagenumber" passed, must be a string and length > 0');
			}

			// mark the message for deletion
			$result = imap_delete($this->mbox, $messagenumber);
			// Delete all messages marked for deletion
			$result = imap_expunge($this->mbox);

			return $result;
		}
		/**
		 * Check and return if there are attachments inside the email
		 *
		 * @param string $messagenumber
		 *
		 * @throws RuntimeException if imap server was not connected
		 * @throws InvalidArgumentException if $messagenumber is not a string or length < 1
		 *
		 * @return array $attachments_list
		 */
		public function read_attachment_list($messagenumber = ''){
			// verify that the mailbox stream is connected
			if($this->mbox === false) {
				throw new RuntimeException('imap server was not connected.');
			}

			// verify parameter meets the expected contract
			if(is_string($messagenumber) !== true || strlen(trim($messagenumber)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "messagenumber" passed, must be a string and length > 0');
			}

			 $p = imap_fetchstructure($this->mbox,$messagenumber);

			// PARAMETERS
			// get all parameters, like charset, filenames of attachments, etc.
			$params = array();
			$attachments_list = array();
			if ($p->parameters)
			foreach ($p->parameters as $x)
				$params[strtolower($x->attribute)] = $x->value;
			if ($p->dparameters)
			foreach ($p->dparameters as $x)
				$params[strtolower($x->attribute)] = $x->value;

			// ATTACHMENT
			// Any part with a filename is an attachment,
			// so an attached text file (type 0) is not mistaken as the message.
			if(isset($params['filename']) || isset($params['name'])){
				if(strlen($param['filename'])>0 || strlen($param['name'])>0){
					if ($params['filename'] || $params['name']) {
						// filename may be given as 'Filename' or 'Name' or both
						$filename = ($params['filename'])? $params['filename'] : $params['name'];

						array_push($attachments_list,$filename);
					}
				}
			}
			return $attachments_list;
		}

		/**
		 * body
		 * returns the message inside the email
		 * @see http://www.php.net/manual/en/function.imap-body.php
		 * @param string $messagenumber
		 * @throws RuntimeException if imap server was not connected
		 * @throws InvalidArgumentException if $messagenumber is not a string or length < 1
		 * @return string $result the body inside the email
		 */

		public function body($messagenumber = ''){
			// verify that the mailbox stream is connected
			if($this->mbox === false) {
				throw new RuntimeException('imap server was not connected.');
			}

			// verify parameter meets the expected contract
			if(is_string($messagenumber) !== true || strlen(trim($messagenumber)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "messagenumber" passed, must be a string and length > 0');
			}

			// get the message body
			$result = imap_body($this->mbox, $messagenumber);

			return $result;
		}

		/**
		 * Fetch email body as html format.
		 *
		 * @see http://php.net/manual/en/function.imap-fetchbody.php
		 * @param string $messagenumber
		 * @throws RuntimeException if imap server was not connected
		 * @throws InvalidArgumentException if $messagenumber is not a string or length < 1
		 * @return string $result the body inside the email
		 */
		public function fetch_body($messagenumber = '') {
			// verify that the mailbox stream is connected
			if($this->mbox === false) {
				throw new RuntimeException('imap server was not connected.');
			}

			// verify parameter meets the expected contract
			if(is_string($messagenumber) !== true || strlen(trim($messagenumber)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "messagenumber" passed, must be a string and length > 0');
			}

			// get the html section of the email by using 2 parameter
			$body = imap_fetchbody($this->mbox, $messagenumber, '2');

			// return printable format
			return quoted_printable_decode($body);
		}
	}
