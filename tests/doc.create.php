<?php

require_once('cushion.class.php');

try
{
	$cushion = new Cushion();
	$cushion->db_select('mydb');

	$doc = $cushion->doc_create(Array(
		'foo' => 'foo',
		'key' => Array(
			'subkey' => 1,
			'subkey2' => 'value'
		)
	), 'test_doc_id');
}
catch (CouchException $e)
{
	echo $e->__toString();
}

?>