<?php

require_once('../lib/cushion.class.php');

try
{
	# GET http://localhost:5984/test
	$db1 = Database::Select(new Server, 'test');

	# GET /test/_design/testing
	$designdoc = $db1->GetDesignDocument('testing');

	# GET /test/_design/testing/view/all
	$view = $designdoc->GetView('all');

	# GET /test/_design/testing/view/all
	print_r($view->Map());

	# GET /test/_design/testing/view/all?reduce=true
	print_r($view->Reduce());
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>