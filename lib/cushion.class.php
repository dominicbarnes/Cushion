<?php

require_once('couch.exception.php');	// CouchException
require_once('server.class.php');	// Server Interface
require_once('database.class.php');	// Database Interface
require_once('document.class.php');	// Document Interface
require_once('designdoc.class.php');	// Design Document Interface (extends Document, not Cushion)
require_once('view.class.php');		// View Interface

/**
 * Performs the actual work of making the HTTP request to the server, parsing the response and processing errors
 *
 * @package cushion
 */
abstract class Cushion {
	/**
	 * @var boolean Determines whether or not to output useful debugging information
	 * @access public
	 */
	public $debug = false;

	/**
	 * @var array Stores information about the database that server that is being used
	 * @access public
	 */
	public $info = Array();

	/**
	 * @var string The httpauth option (concatenated by $this->user && $this->passwd)
	 * @access private
	 */
	protected $_auth;

	/**
	 * @var string Stores the URI for the resource extending this class
	 * @access private
	 */
	protected $_uri;

	protected $_uri_pieces;

	/**
	 * http://wiki.apache.org/couchdb/HTTP_status_list
	 *
	 * @var array HTTP Status Codes and their corresponding messages (number => message)
	 * @access public
	 */
	public static $httpmessages = Array(
		// [Informational 1xx]
		100 => 'Continue',
		101 => 'Switching Protocols',
		// [Successful 2xx]
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		// [Redirection 3xx]
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '(Unused)',
		307 => 'Temporary Redirect',
		// [Client Error 4xx]
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Resource Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		// [Server Error 5xx]
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'
	);

	public function GetURI() {
		return $this->_uri;
	}

	protected function _setURI() {
		return $this->_uri = $this->_buildURI();
	}

	private function _buildURI($append_path = null, $append_query = null) {
		$uri = $this->_uri_pieces;

		if (isset($append_path)) {
			if (is_array($append_path)) {
				$uri['path'] = array_merge($uri['path'], $append_path);
			} else {
				$uri['path'][] = $append_path;
			}
		}
		if (!empty($uri['path']))
			$uri['path'] = '/' . implode('/', $uri['path']);

		if (isset($append_query) && is_array($append_query)) {
			foreach ($append_query as $key => $value) {
				if (is_bool($value))
					$append_query[$key] = $this->_boolString($value);
			}

			if (!empty($uri['query']))
				$uri['query'] = array_merge($uri['query'], $append_query);
			else
				$uri['query'] = $append_query;
		}
		if (!empty($uri['query']))
			$uri['query'] = http_build_query($uri['query']);

		return http_build_url('http://localhost/', $uri);
	}

	private function _boolString($value) {
		return $value ? 'true' : 'false';
	}


	/**
	 * Executes an HTTP request to the specified URI, throws a CouchException if an error is detected in the response.
	 *
	 * @access protected
	 * @param array $data The JSON data to be included in the request (default: null)
	 * @param const $method The PECL_HTTP constant defining the HTTP Method (ie. POST, GET, etc.) to be used (default: HTTP_METH_GET)
	 * @param array $options Array of additional options (including additional headers) to be sent with the HTTP request
	 * @return array The json_decoded response received
	 */
	protected function _execute($method = HTTP_METH_GET, $data = null, $append_path = null, $append_query = null, $options = null, $raw_data = false, $json_response = true) {
		$info = Array();

		$defaults = Array( 'cookies' => $_COOKIE );
		if ($json_response) {
			$defaults['headers'] = Array(
				'Content-Type' => 'application/json',
				'Accept' => 'application/json'
			);
		}

		if (isset($this->_auth))	$defaults['httpauth'] = $this->_auth;
		$options = isset($options) ? array_merge($defaults, $options) : $defaults;

		$this->_setURI();
		$uri = (isset($append_path) || isset($append_query)) ? $this->_buildURI($append_path, $append_query) : $this->_uri;
		$data = $raw_data ? http_build_query($data) : json_encode($data);

		$output = http_parse_message(http_request($method, $uri, $data, $options, $info));

		if ($this->debug) {
			echo '<pre>';
			echo 'Output: ' . htmlspecialchars(print_r($output, true));
			echo 'HTTP Info: ' . htmlspecialchars(print_r($info, true));
			echo 'Options: ' . htmlspecialchars(print_r($options, true));
			echo 'Data: ' . htmlspecialchars(print_r($data, true));
			echo '</pre>';
		}

		if (isset($output->headers['Set-Cookie'])) {
			$oCookie = http_parse_cookie($output->headers['Set-Cookie']);
			setcookie('AuthSession', $oCookie->cookies['AuthSession'], time() + 600, $oCookie->path);
		}

		$output = ($json_response) ? json_decode($output->body, true) : $output->body;

		if (is_array($output) && isset($output['error'])) {
			throw new CouchException($output['error'], $output['reason'], $info['response_code'], $uri);
		}

		return $output;
	}
}