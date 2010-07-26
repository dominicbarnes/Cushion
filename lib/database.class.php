<?php

/**
 * Interface to check the connection to the CouchDB Database
 *
 * @package cushion
 */
class Database extends Cushion {
	public $info = Array();

	/**
	 * @var string The name of the database being used (to use another database, create another instance of Cushion)
	 * @access public
	 */
	private $_name;

	private $_active = false;


	public static function Create($server, $name) {
		$d = new Database;

		$d->_create($server, $name);

		return $d;
	}

	public static function Select($server, $name, $create = false) {
		$d = new Database;

		$d->_select($server, $name, $create);

		return $d;
	}

	private function _create($server, $name) {
		$this->_uri_pieces = $server->_uri_pieces;
		$this->_uri_pieces['path'][0] = $name;
		$this->_setURI();

		$this->_execute(HTTP_METH_PUT);
	}

	private function _select($server, $name, $create = false) {
		try {
			$this->_uri_pieces = $server->_uri_pieces;
			$this->_uri_pieces['path'][0] = $name;
			$this->_setURI();

			return $this->info['general'] = $this->_execute();
		} catch (CouchException $e) {
			if ($create && $e->getType() == 'not_found' && $e->getMessage() == 'no_db_file') {
				return Database::Create($server, $name);
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Retrieves information about a database (also to test for existence)
	 *
	 * @access public
	 * @param type $uri The full URI for the database
	 * @return array The response from the CouchDB server
	 */
	public function Info() {
		return $this->info['general'] = $this->_execute();
	}

	public function AllDocs($include_docs = false) {
		return $this->_execute(null, null, '_all_docs', Array('include_docs' => $include_docs));
	}

	public function Changes($params = Array()) {
		return $this->_execute(null, null, '_changes', $params);
	}

	public function Compact() {
		return $this->_execute(HTTP_METH_POST, null, '_compact');
	}

	public function SetOption($name, $value) {
		$result = Array(
			'result' => 'failure',
			'option' => $name,
			'value' => $value,
			'response' => null
		);

		try {
			$result['response'] = $this->_execute(HTTP_METH_PUT, $value, $name);
			$result['result'] = 'success';
		} catch (CouchException $ce) {
			$result['response'] = $ce->getArray();
		}

		return $result;
	}

	public function SetOptions($options) {
		$results = Array();
		foreach ($options as $name => $value) {
			$results[] = $this->SetOption($name, $value);
		}
		return $results;
	}


	public function CreateDocument($doc, $id = null) {
		return Document::Create($this, $doc, $id);
	}

	public function CreateDesignDocument($designdoc_name, $views, $shows = null, $lists = null, $updates = null, $validate = null) {
		return DesignDocument::Create($this, $designdoc_name, $views, $shows, $lists, $updates, $validate);
	}

	public function GetDocument($id = null, $rev = null) {
		if (!isset($id) && !isset($rev))
			return $this->AllDocs();

		return Document::Get($this, $id, $rev);
	}

	public function GetDesignDocument($name, $rev = null) {
		return DesignDocument::Get($this, $name, $rev);
	}

	public function DeleteDocument($id, $rev = null) {
		$d = Document::Get($this, $id, $rev);
		return $d->Delete();
	}
}