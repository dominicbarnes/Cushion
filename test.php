<?php

require_once('lib/cushion.class.php');

$srv = new Server;
$db1 = Database::Select($srv, 'db1', true);

$designdoc = $db1->GetDesignDocument('testing');

$view = $designdoc->GetView('all');

print_r($view->Map());

?>