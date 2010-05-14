<?php

require_once('../lib/cushion.class.php');

try
{
	# GET http://localhost:5984/test
	$db = Database::Select(new Server, 'test', true);

	# GET /test/test_doc_id
	$doc = $db->GetDocument('test-document-1');

	// Output current document (note _rev)
	print_r($doc);

	// Change a value in the document
	$doc->doc['foo'] = 'bar123456789';

	# POST /mydb/test_doc_id
	$doc->Update();

	// Output new document (note _rev has changed)
	print_r($doc);
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>