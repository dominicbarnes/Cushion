<?php

require_once('../cushion.class.php');

try
{
	$cushion = new Cushion();

	# Outputs the response received from the CouchDB Server (includes Version information)
	echo '<pre>';
	print_r($cushion->info['couch']);
	echo '</pre>';
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>