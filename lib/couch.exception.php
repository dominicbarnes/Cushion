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
	private $type;

	/**
	 * Constructor Method :: Takes in the information given, assigns the internal properties and gets the Status Code message for the HTTP response
	 * Typical Error Respons: {"error": "[type]", "reason": "[message]"}
	 *
	 * @param string $type The type of error according to the CouchDB response
	 * @param string $message The reason for the error according to the CouchDB response
	 * @param integer $code The HTTP Response Code
	 */
	function __construct($type, $message, $code) {
		// make sure everything is assigned properly
		parent::__construct($message, $code);

		$this->type = $type;
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
		return __CLASS__ . ": [{$this->code} {$this->code_message}] [{$this->type}]: {$this->message} \n";
	}

	public function getType() {
		return $this->type;
	}

	public function getArray() {
		return Array(
			'type' => $this->type,
			'message' => $this->message,
			'httpcode' => $this->code,
			'httpresponse' => Cushion::$httpmessages[$code]
		);
	}
}