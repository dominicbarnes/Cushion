<?php

require_once('../lib/cushion.class.php');

try
{
	# GET http://localhost:5984/test
	$db = Database::Select(new Server, 'test', true);

	# GET /test/202086a8d868f65d6817e2ac342e7e9f
	$doc = $db->GetDocument('test-document-1');

	// Output the document itself
	print_r($doc->doc);

	# DELETE /test/202086a8d868f65d6817e2ac342e7e9f
	print_r($doc->Delete());
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>