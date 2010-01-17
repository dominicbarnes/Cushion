<?php

require_once('../cushion.class.php');

$cushion = new Cushion();
$cushion->db_select('mydb');

echo '<pre>';
print_r($cushion->info['database']);
echo '</pre>';

?>