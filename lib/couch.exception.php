<?php

/**
 * Custom Exception for CouchDB errors
 *
 * @package cushion
 */
class CouchException extends Exception {
	/**
	 * @var string Additional information about this Exception. The type of error specified by the CouchDB response {"error": "[type]", "reason": "[message]"}
	 * @access private
	 */
	private $_type;

	private $_uri;

	/**
	 * Constructor Method :: Takes in the information given, assigns the internal properties and gets the Status Code message for the HTTP response
	 * Typical Error Respons: {"error": "[type]", "reason": "[message]"}
	 *
	 * @param string $type The type of error according to the CouchDB response
	 * @param string $message The reason for the error according to the CouchDB response
	 * @param integer $code The HTTP Response Code
	 */
	function __construct($type, $message, $code, $uri) {
		// make sure everything is assigned properly
		parent::__construct($message, $code);

		$this->_type = $type;
		$this->_uri = $uri;
		$this->message = $message;
		$this->code = $code;
		$this->code_message = Cushion::$httpmessages[$code];
	}

	/**
	 * Custom string representation of the exception
	 *
	 * @access public
	 * @return string Formatted exception message
	 */
	public function __toString() {
		return __CLASS__ . ": [{$this->code} {$this->code_message}] [{$this->_type}]: {$this->message} :: <a href=\"{$this->_uri}\">{$this->_uri}</a>\n";
	}

	public function getType() {
		return $this->_type;
	}

	public function getArray() {
		return Array(
			'type' => $this->_type,
			'message' => $this->message,
			'httpcode' => $this->code,
			'httpresponse' => Cushion::$httpmessages[$code]
		);
	}
}