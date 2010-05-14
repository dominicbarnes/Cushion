<?php

require_once('../lib/cushion.class.php');

try
{
	# GET http://localhost:5984/
	$srv = new Server;

	# GET /test
	$db = $srv->DatabaseSelect('test', true); // 2nd param = Will create if it doesn't already exist

	# GET /test
	print_r($db->Info());

	# GET /test/_all_docs
	print_r($db->AllDocs());
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>