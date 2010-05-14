<?php

/**
 * Represents a CouchDB Document
 *
 * @package cushion
 */
class Document extends Cushion {
	/**
	 * @var array Array that reflects the Documents structure
	 * @access public
	 */
	public $doc;

	/**
	 * @var string The _id for the Document
	 * @access private
	 */
	private $_id;

	/**
	 * @var string The _rev for the Document
	 * @access private
	 */
	private $_rev;

	private $_active = false;


	public static function Create($db, $doc, $id = null) {
		$d = new Document;

		$d->_create($db, $doc, $id);

		return $d;
	}

	public static function Get($db, $id = null, $rev = null) {
		$d = new Document;

		$d->_get($db, $id, $rev);

		return $d;
	}

	public function Update() {
		$this->_update();
	}

	public function Delete() {
		$this->_delete();
	}

	/**
	 * Creates a new CouchDB document
	 *
	 * @access public
	 * @param string $baseuri The URI identifying the server and database
	 * @param array $doc The Document data (multi-dimensional array)
	 * @param string $id The _id for this new document (default: null)
	 * @return array The response from the CouchDB server
	 */
	private function _create($db, $doc, $id = null) {
		$this->_uri_pieces = $db->_uri_pieces;
		if (isset($id))	$this->_uri_pieces['path'][1] = $id;

		$output = $this->_execute(isset($id) ? HTTP_METH_PUT : HTTP_METH_POST, $doc);

		$this->_uri .= (isset($id)) ? $output['id'] : '';
		$this->_id = $output['id'];
		$this->_rev = $output['rev'];
		$this->doc = $doc;

		$this->_uri_pieces['query']['rev'] = $output['rev'];

		$this->_active = true;

		return $output;
	}

	/**
	 * Retrieves an existing document based on an _id and _rev
	 *
	 * @access public
	 * @param string $uri The full URI for the document
	 * @return array The response from the CouchDB server
	 */
	public function _get($db, $id, $rev = null) {
		$this->_uri_pieces = $db->_uri_pieces;
		$this->_uri_pieces['path'][1] = $id;
		if (isset($rev))
			$this->_uri_pieces['query']['rev'] = $rev;

		$output = $this->_execute();

		$this->_id = $output['_id'];
		$this->_rev = $output['_rev'];
		$this->_uri_pieces['query']['rev'] = $output['_rev'];
		$this->doc = $output;

		$this->_active = true;

		return $output;
	}

	/**
	 * Takes the data stored in $this->doc and uses it to update the existing document
	 *
	 * @access public
	 * @return array The response from the CouchDB server
	 */
	public function _update() {
		if ($this->_active) {
			$output = $this->_execute(HTTP_METH_PUT, $this->doc);

			$this->_uri_pieces['query']['rev'] = $this->_rev = $this->doc['_rev'] = $output['rev']; // Update the REV in 3 places

			return $output;
		} else {
			throw new Exception('Error, Document no longer active. Did you DELETE it?');
		}
	}

	/**
	 * Deletes the document from the server
	 *
	 * @access public
	 * @return array The response from the CouchDB server
	 */
	public function _delete() {
		if ($this->_active) {
			$this->_execute(HTTP_METH_DELETE);

			$this->_active = false;
		} else {
			throw new Exception('Error, Document no longer active. Did you DELETE it?');
		}
	}

	/**
	 * Creates a copy of the existing document to another specified ID
	 *
	 * @access public
	 * @param string $to_id The _id for the new document you will be creating
	 * @return array The response from the CouchDB server
	 */
	public function _copy($to_id) {
		return $this->_execute(HTTP_METH_COPY, null, null, null, Array(
			'headers' => Array('Destination' => $to_id)
		));
	}
}