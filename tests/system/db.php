<?php
	/**
	 * DB
	 * @see http://www.php.net/manual/en/class.mysqli.php
	 */
	class DB {
		/**
		 * @var stdClass $mysqli
		 */
		private $mysqli;

		/**
		 * constructor
		 * connect to mysql server and select a database
		 * @see http://www.php.net/manual/en/mysqli.construct.php
		 * @param string $hostname
		 * @param string $username
		 * @param string $password
		 * @param string $database_name
		 * @throws InvalidArgumentException if $hostname is not a string or length < 1
		 * @throws InvalidArgumentException if $username is not a string or length < 1
		 * @throws InvalidArgumentException if $password is not a string or length < 1
		 * @throws InvalidArgumentException if $database_name is not a string or length < 1
		 * @throws RuntimeException if unable to connect to the database
		 * @throws RuntimeException if unable to select a database
		 * @return void
		 */
		public function __construct($hostname = '', $username = '', $password = '', $database_name = '') {
			// verify parameter meets the expected contract
			if(is_string($hostname) !== true || strlen(trim($hostname)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "hostname" passed, must be a string and lenght > 0');
			}

			// verify parameter meets the expected contract
			if(is_string($username) !== true || strlen(trim($username)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "username" passed, must be a string and lenght > 0');
			}

			// verify parameter meets the expected contract
			if(is_string($password) !== true || strlen(trim($password)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "password" passed, must be a string and lenght > 0');
			}

			// verify parameter meets the expected contract
			if(is_string($database_name) !== true || strlen(trim($database_name)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "database_name" passed, must be a string and lenght > 0');
			}

			// instantiate the mysqli object
			$this->mysqli = new mysqli($hostname, $username, $password, $database_name);

			// verify that it wass able to connect to the mysql server and was able to select the database
			if(is_object($this->mysqli) !== true) {
				throw new RuntimeException('Unable to connect to the database: ' . $this->mysqli->connect_error);
			}
		}

		/**
		 * close
		 * close a MySQL connection
		 * @see http://www.php.net/manual/en/mysqli.close.php
		 * @throws RuntimeException if unable to close the connection
		 * @return void
		 */
		public function close() {
			if($this->mysqli->close() !== true) {
				throw new RuntimeException('Unable to close the mysql connection.');
			}
		}

		/**
		 * query
		 * performs MySQL query
		 * @see http://www.php.net/manual/en/mysqli.query.php
		 * @param string $sql
		 * @throws InvalidArgumentException if $sql is not a string or length < 1
		 * @throws RuntimeException if an error is encountered
		 * @return stdClass $result
		 */
		public function query($sql = '') {
			// verify parameter meets the expected contract
			if(is_string($sql) !== true || strlen(trim($sql)) < 1) {
				throw new InvalidArgumentException('Invalid parameter "sql" passed, must be a string and length > 0');
			}

			// execute the query
			$result = $this->mysqli->query($sql);

			// when an error is encountered
			if(isset($this->mysqli->error) === true && strlen(trim($this->mysqli->error)) > 0) {
				throw new RuntimeException($this->mysqli->error);
			}

			return $result;
		}

		/**
		 * next_result
		 * @see http://www.php.net/manual/en/mysqli.next-result.php
		 * @throws RuntimeException if unable to prepare the next result
		 * @return void
		 */
		public function next_result() {
			$result = $this->mysqli->next_result();

			if(is_bool($result) !== true || $result !== true) {
				throw new RuntimeException('Unable to prepare the next result: ' . $this->mysqli->error);
			}
		}

		/**
		 * affected_rows
		 * get the number of affected rows
		 * @see http://www.php.net/manual/en/mysqli.affected-rows.php
		 * @throws RuntimeException if returned number < 0
		 * @return int $rows
		 */
		public function affected_rows() {
			$rows = $this->mysqli->affected_rows;

			if(is_int($rows) !== true || $rows < 0) {
				throw RuntimeException($this->mysqli->error);
			}

			return $rows;
		}

		/**
		 * insert_id
		 * get the last inserted id from query
		 * @see http://www.php.net/manual/en/mysqli.insert-id.php
		 * @throws RuntimeException if returned number is not an integer
		 * @return int $rows
		 */
		public function insert_id() {
			$last_id = $this->mysqli->insert_id;

			if(is_int($last_id) !== true) {
				throw RuntimeException($this->mysqli->error);
			}

			return $last_id;
		}

		/**
		 * escape the string using MySQLi builtin
		 * @param string $str
		 * @throws InvalidArgumentException when the argument is an array, object or resource
		 * @return	string $str
		 */
		public function escape($str) {
			// only accept valid argument
			if(is_array($str) === true || is_object($str) === true || is_resource($str) === true) {
				throw new InvalidArgumentException('Invalid parameter "str", cannot accept array, object or resource data types');
			}

			// wrap with quotes as implemented in CodeIgniter DB_driver.php
			$str = "'" . $this->mysqli->real_escape_string($str) . "'";

			return $str;
		}

		/**
		 * Special function for calling stored procedures instead of using query()
		 * @param string $sql
		 * @param bool $expecting_result If caller is expecting a resultset returned by the stored proc
		 * @return stdClass Contains the status_code plus the defined $expected_return_values
		 * @throws InvalidArgumentException		 *
		 * @throws RuntimeException
		 *
		 * @TODO need Engineer Experience (EX)
		 */
		public function call_stored_procedure($sql, $expected_return_values = array(), $with_result = TRUE) {
			if (!is_string($sql) || strlen($sql) < 1) {
				throw new InvalidArgumentException('"sql" parameter must be a string');
			}

			if(!is_array($expected_return_values)){
				throw new InvalidArgumentException('"expected_return_values" parameter must be an array');
			}

			$return_object = new stdClass();

			$this->mysqli->query($sql);

			if($this->mysqli->errno !== 0) {
				throw new RuntimeException("SQL error: ". $this->mysqli->error);
			}

			//in case the stored procedure doesn't follow standard status_code return value
			if($with_result === TRUE) {
				//get the status code and expected results
				$additional_values_arr = array('@status_code AS `status_code`');

				foreach($expected_return_values AS $val) {
					$additional_values_arr[] = "@{$val} AS `{$val}`";
				}

				// SELECT @status_code AS `status_code`, @some_insert_id AS `some_insert_id`
				$return_sql = "SELECT ". implode(',', $additional_values_arr);

				$res = $this->mysqli->query($return_sql);

				if($this->mysqli->errno !== 0) {
					throw new RuntimeException('Mysqli query failed: '. $this->mysqli->error);
				}

				// it's always 1 record
				$return_object = $res->fetch_object();
			}

			while($this->mysqli->more_results()){
				$this->mysqli->next_result();
			}

			return $return_object;
		}

	}