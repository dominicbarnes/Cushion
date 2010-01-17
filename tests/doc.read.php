<?php

require_once('../cushion.class.php');

$cushion = new Cushion();
$cushion->db_select('mydb');

$doc = $cushion->doc_read('test_doc_id');

?>