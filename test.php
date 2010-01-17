<pre>
<?php

require_once('cushion.class.php');

try
{
	$cushion = new Cushion('applog');
	$cushion->debug = true;

	#print_r($cushion->info);
	$doc = $cushion->doc_create(Array(
		'key' => 'value',
		'key2' => 'value'
	), 'test');

	$doc->delete();
}
catch (Exception $e)
{
	exit($e->__toString());
}

?>
</pre>