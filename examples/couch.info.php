<?php

require_once('../lib/cushion.class.php');

try
{
	# GET http://localhost:5984/
	$srv = new Server;

	echo '<pre>';

	# GET /
	print_r($srv->Info());

	# GET /_all_dbs
	print_r($srv->AllDbs());

	# GET /_config
	print_r($srv->Config());

	# GET /_stats
	print_r($srv->Stats());

	# GET /_active_tasks
	print_r($srv->ActiveTasks());

	echo '</pre>';
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>