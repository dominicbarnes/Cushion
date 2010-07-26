<?php

/**
 * Interface to check the connection to the CouchDB Server
 *
 * @package cushion
 */
class Server extends Cushion {
	/**
	 * Constructor Method :: Establishes connection to server (uses defaults) and database (only if $database is defined)
	 *
	 * @access public
	 * @param string $database The name of the CouchDB database (default: null)
	 * @param string $host The hostname of the CouchDB server (default: 'localhost')
	 * @param string $protocol The protocol being used to connect to connect (default: 'http')
	 * @param integer $port The port number being used (default: 5984)
	 * @param string $user The username for httpauth (default: null)
	 * @param string $passwd The password for httpauth (default: null)
	 * @return void
	 */
	function __construct($host = 'localhost', $protocol = 'http', $port = 5984, $user = null, $passwd = null) {
		$this->_uri_pieces['host'] = $host;
		$this->_uri_pieces['scheme'] = $protocol;
		$this->_uri_pieces['port'] = $port;

		if (isset($user) && isset($passwd))
			$this->_auth = $user . ':' . $passwd;

		$this->_setURI();
	}

	public function DatabaseSelect($name, $create = false) {
		return Database::Select($this, $name, $create);
	}

	/**
	 * Retrieves information about a CouchDB server (also to test for existence)
	 *
	 * @access public
	 * @return array The response from the CouchDB server
	 */
	public function Info() {
		return $this->_execute();
	}

	public function AllDbs() {
		return $this->info['all_dbs'] = $this->_execute(null, null, '_all_dbs');
	}

	public function Config() {
		return $this->info['config'] = $this->_execute(null, null, '_config');
	}

	public function Uuids($count = 1) {
		return $this->_execute(null, null, '_uuids', Array('count' => $count));
	}

	public function Stats() {
		return $this->_execute(null, null, '_stats');
	}

	public function ActiveTasks() {
		return $this->_execute(null, null, '_active_tasks');
	}

	public function Replicate($source, $target, $create_target = false, $continuous = false) {
		return $this->_execute(HTTP_METH_POST, Array(
			'source' => $source->_uri,
			'target' => $target->_uri,
			'create_target' => $create_target,
			'continuous' => $continuous
		), '_replicate');
	}

	public function Login($username, $password) {
		return $this->_execute(
			HTTP_METH_POST,
			Array(
				'name' => $username,
				'password' => $password
			),
			'_session',
			null,
			Array(
				'headers' => Array(
					'Content-Type' => 'application/x-www-form-urlencoded'
				)
			),
			true
		);
	}

	public function Logout() {
		return $this->_execute(HTTP_METH_DELETE, null, '_session');
	}

	public function Session() {
		return $this->_execute(null, null, '_session');
	}
}