<?php

require_once('../lib/cushion.class.php');

try
{
	# GET http://localhost:5984/test
	$db1 = Database::Select(new Server, 'test', true);

	# GET /test/_design/testing
	$designdoc = $db1->CreateDesignDocument('testing', Array(
		'all' => Array(
			'map' => 'function() { emit(null, doc); }',
			'reduce' => 'function(keys, values) { return keys.length; }'
		)
	));

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