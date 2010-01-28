<?php

require_once('../cushion.class.php');

try
{
	$cushion = new Cushion();
	$cushion->db_select('mydb');

	echo '<pre>';
	print_r($cushion->info['database']);
	echo '</pre>';
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>