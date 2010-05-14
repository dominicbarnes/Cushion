<?php

require_once('../cushion.class.php');

try
{
	# GET http://localhost:5984/test
	$db = Database::Select(new Server, 'test', true);

	# GET /test/test_doc_id
	$doc = $db->doc_read('test-document-1');

	// Output the document
	print_r($doc->doc);
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>