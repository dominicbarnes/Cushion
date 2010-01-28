<?php

require_once('../cushion.class.php');

try
{
	$cushion = new Cushion();
	$cushion->db_select('mydb');

	$doc = $cushion->doc_read('test_doc_id');
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>