# Cushion
_Resting on the Couch with PHP_

## What is the Cushion?

Cushion is a PHP library meant solely for communicating with a CouchDB server.

## How do I use Cushion?

### Dependencies

Cushion requires the `PECL_HTTP` ([website](http://pecl.php.net/package/pecl_http) extension

#### Installing PECL_HTTP

	# pecl install pecl_http

### In Code

There is one class file (`cushion.class.php`) that contains all the components necessary for using the Cushion, all you need to do is include it into your script and you are ready to go!

## Examples

### Creating a Document

    <?php

    require_once('cushion.class.php');

    $cushion = new Cushion();
    $cushion->db_select('mydb');

    $doc = $cushion->doc_create(Array(
		'foo' => 'foo',
		'key' => Array(
			'subkey' => 1,
			'subkey2' => 'value'
		)
    ), 'test_doc_id');

    ?>

### Modifying a Document

    <?php

    require_once('cushion.class.php');

    $cushion = new Cushion();
    $cushion->db_select('mydb');

    $doc = $cushion->doc_read('test_doc_id');

    $doc->doc['foo'] = 'bar';

    $doc->update();

    ?>

### Deleting a Document

    <?php

    require_once('cushion.class.php');

    $cushion = new Cushion();
    $cushion->db_select('mydb');

    $doc = $cushion->doc_read('test_doc_id');

    $doc->delete();
    
	?>

## Relax
Now wasn't that easy?