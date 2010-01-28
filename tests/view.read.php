<?php

require_once('../cushion.class.php');

try
{
	$cushion = new Cushion();
	$cushion->db_select('mydb');

	$doc = $cushion->view_read('designdoc', 'viewname');

	echo '<pre>';
	print_r($doc);
	echo '</pre>';
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>