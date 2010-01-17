<?php

class Cushion
{
	public $protocol;
	public $host;
	public $port;
	public $database;
	public $debug;
	public $info;

	private $uri;
	private $doc;
	private $db;

	function __construct($database = null, $host = 'localhost', $protocol = 'http', $port = 5984)
	{
		$this->protocol = $protocol;
		$this->host = $host;
		$this->port = $port;

		$uri = $protocol . '://' . $host . ':' . $port . '/';

		$couch = new Couch();
		$this->info['couch'] = $couch->info($uri);

		$this->uri = $uri;

		if (isset($database))
			$this->info['database'] = $this->db_select($database);
	}

	public function db_select($name)
	{
		$uri = $this->protocol . '://' . $this->host . ':' . $this->port . '/' . $name . '/';

		$db = new Database();
		$db_info = $db->info($uri);
		
		$this->database = $name;
		$this->uri = $uri;

		return $db_info;
	}

	public function doc_create($data, $id = null)
	{
		if (!isset($this->database))
			throw new Exception('No database selected');
		
		$uri = $this->uri;
		
		$doc = new Document();
		if (isset($this->debug))	$doc->debug = $this->debug;
		$doc->create($uri, $data, $id);

		return $doc;
	}

	public function doc_read($id = null, $rev = null)
	{
		if (!isset($this->database))
			throw new Exception('No database selected');
			
		$uri = $this->uri;
		$uri .= (isset($id)) ? $id : '_all_docs';
		if (isset($rev))	$uri .= '?rev=' . $rev;

		$doc = new Document();
		if (isset($this->debug))	$doc->debug = $this->debug;
		$doc->read($uri);

		return $doc;
	}

	public function view_read($design, $name, $params = null)
	{
		if (!isset($this->database))
			throw new Exception('No database selected');
			
		$uri = $this->uri;
		$uri .= '_design/' . $design . '/_view/' . $name;
		if (isset($params))	$uri .= http_build_query($params);

		$view = new View();
		if (isset($this->debug))	$view->debug = $this->debug;
		$view->read($uri);

		return $view;
	}
}

class Client
{
	public $debug = false;
	private $curl;

	protected function execute($uri, $data = null, $method = HTTP_METH_GET, $options = null)
	{
		$info = Array();

		$defaults = Array(
			'headers' => Array(
				'Content-Type' => 'application/json',
				'Accept' => 'application/json'
			)
		);
		$options = (isset($options)) ? array_merge_recursive($defaults, $options) : $defaults;

		$output = trim(http_parse_message(http_request($method, $uri, $data, $options, $info))->body);

		if ($this->debug)
		{
			echo 'Output: ' . $output . "\n\n";
			echo 'HTTP Info: ';
			print_r($info);
		}

		$output = json_decode($output, true);

		if (isset($output['error']))
			throw new CouchException($output['error'], $output['reason'], $info['response_code']);

		return $output;
	}
}

class Document extends Client
{
	public $doc;
	
	private $uri;
	private $id;
	private $rev;

	function __construct($baseuri = null, $id = null, $rev = null, $doc = null)
	{
		if (isset($baseuri) && isset($id) && isset($rev) && isset($doc))
		{
			$this->uri = $baseuri . $id . '?rev=' . $rev;
			$this->id = $id;
			$this->rev = $rev;
			$this->doc = $doc;
		}
	}

	public function create($baseuri, $doc, $id = null)
	{
		$this->uri = $baseuri;
		if (isset($id))	$this->uri .= $id;

		$output = $this->execute($this->uri, json_encode($doc), (isset($id)) ? HTTP_METH_PUT : HTTP_METH_POST);

		$this->id = $output['id'];
		$this->rev = $output['rev'];
		$this->doc = $doc;

		$this->uri .= $output['id'];

		return $output;
	}

	public function read($uri)
	{
		$this->uri = $uri;
		
		$output = $this->execute($uri);

		$this->id = $output['_id'];
		$this->rev = $output['_rev'];
		$this->doc = $output;

		return $output;
	}

	public function update()
	{
		$output = $this->execute($this->uri, json_encode($this->doc), HTTP_METH_POST);

		$this->rev = $output['rev'];

		return $output;
	}

	public function delete()
	{
		$uri = $this->uri . '?rev=' . $this->rev;

		unset($this->uri);
		unset($this->id);
		unset($this->rev);
		unset($this->doc);
		
		return $this->execute($uri, null, HTTP_METH_DELETE);
	}

	public function copy($to_id)
	{
		return $this->execute($this->uri, null, HTTP_METH_COPY, Array(
			'headers' => Array('Destination' => $to_id)
		));
	}
}

class Couch extends Client
{
	private $uri;

	public function info($uri)
	{
		$this->uri = $uri;

		return $this->execute($uri);
	}
}

class Database extends Client
{
	private $uri;

	public function info($uri)
	{
		$this->uri = $uri;

		return $this->execute($uri);
	}
}

class View extends Client
{
	private $uri;
	
	public function read($uri, $params = null)
	{
		$this->uri = $uri;
		if (isset($params))	$this->uri .= http_build_query($params);

		return $this->execute($uri);
	}
}

class CouchException extends Exception
{
	private $type;
	
	public function __construct($type, $message, $code = 0)
	{
		// make sure everything is assigned properly
		parent::__construct($message, $code);
		
		$this->type = $type;
		$this->message = $message;
		$this->code = $code;
		$this->code_message = StatusCodes::getMessageForCode($code);
	}

	// custom string representation of object
	public function __toString()
	{
		return __CLASS__ . ": [{$this->code_message}] [{$this->type}]: {$this->message} \n";
	}
}

/**
 * StatusCodes provides named constants for
 * HTTP protocol status codes. Written for the
 * Recess Framework (<a class="linkclass" href="http://www.recessframework.com/">http://www.recessframework.com/</a>)
 *
 * @author Kris Jordan
 * @license MIT
 * @package recess.http
 */
class StatusCodes {
	// [Informational 1xx]
	const HTTP_CONTINUE = 100;
	const HTTP_SWITCHING_PROTOCOLS = 101;
	// [Successful 2xx]
	const HTTP_OK = 200;
	const HTTP_CREATED = 201;
	const HTTP_ACCEPTED = 202;
	const HTTP_NONAUTHORITATIVE_INFORMATION = 203;
	const HTTP_NO_CONTENT = 204;
	const HTTP_RESET_CONTENT = 205;
	const HTTP_PARTIAL_CONTENT = 206;
	// [Redirection 3xx]
	const HTTP_MULTIPLE_CHOICES = 300;
	const HTTP_MOVED_PERMANENTLY = 301;
	const HTTP_FOUND = 302;
	const HTTP_SEE_OTHER = 303;
	const HTTP_NOT_MODIFIED = 304;
	const HTTP_USE_PROXY = 305;
	const HTTP_UNUSED= 306;
	const HTTP_TEMPORARY_REDIRECT = 307;
	// [Client Error 4xx]
	const errorCodesBeginAt = 400;
	const HTTP_BAD_REQUEST = 400;
	const HTTP_UNAUTHORIZED  = 401;
	const HTTP_PAYMENT_REQUIRED = 402;
	const HTTP_FORBIDDEN = 403;
	const HTTP_NOT_FOUND = 404;
	const HTTP_METHOD_NOT_ALLOWED = 405;
	const HTTP_NOT_ACCEPTABLE = 406;
	const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
	const HTTP_REQUEST_TIMEOUT = 408;
	const HTTP_CONFLICT = 409;
	const HTTP_GONE = 410;
	const HTTP_LENGTH_REQUIRED = 411;
	const HTTP_PRECONDITION_FAILED = 412;
	const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
	const HTTP_REQUEST_URI_TOO_LONG = 414;
	const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
	const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const HTTP_EXPECTATION_FAILED = 417;
	// [Server Error 5xx]
	const HTTP_INTERNAL_SERVER_ERROR = 500;
	const HTTP_NOT_IMPLEMENTED = 501;
	const HTTP_BAD_GATEWAY = 502;
	const HTTP_SERVICE_UNAVAILABLE = 503;
	const HTTP_GATEWAY_TIMEOUT = 504;
	const HTTP_VERSION_NOT_SUPPORTED = 505;

	private static $messages = array(
		// [Informational 1xx]
		100=>'100 Continue',
		101=>'101 Switching Protocols',
		// [Successful 2xx]
		200=>'200 OK',
		201=>'201 Created',
		202=>'202 Accepted',
		203=>'203 Non-Authoritative Information',
		204=>'204 No Content',
		205=>'205 Reset Content',
		206=>'206 Partial Content',
		// [Redirection 3xx]
		300=>'300 Multiple Choices',
		301=>'301 Moved Permanently',
		302=>'302 Found',
		303=>'303 See Other',
		304=>'304 Not Modified',
		305=>'305 Use Proxy',
		306=>'306 (Unused)',
		307=>'307 Temporary Redirect',
		// [Client Error 4xx]
		400=>'400 Bad Request',
		401=>'401 Unauthorized',
		402=>'402 Payment Required',
		403=>'403 Forbidden',
		404=>'404 Not Found',
		405=>'405 Method Not Allowed',
		406=>'406 Not Acceptable',
		407=>'407 Proxy Authentication Required',
		408=>'408 Request Timeout',
		409=>'409 Conflict',
		410=>'410 Gone',
		411=>'411 Length Required',
		412=>'412 Precondition Failed',
		413=>'413 Request Entity Too Large',
		414=>'414 Request-URI Too Long',
		415=>'415 Unsupported Media Type',
		416=>'416 Requested Range Not Satisfiable',
		417=>'417 Expectation Failed',
		// [Server Error 5xx]
		500=>'500 Internal Server Error',
		501=>'501 Not Implemented',
		502=>'502 Bad Gateway',
		503=>'503 Service Unavailable',
		504=>'504 Gateway Timeout',
		505=>'505 HTTP Version Not Supported'
	);

	public static function httpHeaderFor($code) {
		return 'HTTP/1.1 ' . self::$messages[$code];
	}

	public static function getMessageForCode($code) {
		return self::$messages[$code];
	}

	public static function isError($code) {
		return is_numeric($code) && $code >= self::HTTP_BAD_REQUEST;
	}

	public static function canHaveBody($code) {
		return
			// True if not in 100s
			($code < self::HTTP_CONTINUE || $code >= self::HTTP_OK)
			&& // and not 204 NO CONTENT
			$code != self::HTTP_NO_CONTENT
			&& // and not 304 NOT MODIFIED
			$code != self::HTTP_NOT_MODIFIED;
	}
}

?>