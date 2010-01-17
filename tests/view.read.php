<?php

require_once('../cushion.class.php');

$cushion = new Cushion();
$cushion->db_select('mydb');

$doc = $cushion->view_read('designdoc', 'viewname');

echo '<pre>';
print_r($doc);
echo '</pre>';

?>