<?php

require_once('../lib/cushion.class.php');

try
{
	# GET http://localhost:5984/test
	$db = Database::Select(new Server, 'test', true);

	# GET /test/_all_docs
	print_r($db->AllDocs());

	# POST /test/
	# {"foo":"foo","key":{"subkey":1,"subkey2":"value"}}
	$doc = $db->CreateDocument(Array(
		'foo' => 'foo',
		'key' => Array(
			'subkey' => 1,
			'subkey2' => 'value'
		)
	), 'test-document-1');

	// Output the document
	print_r($doc->doc);

	# GET /test/_all_docs
	print_r($db->AllDocs());
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>