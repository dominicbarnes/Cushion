# Cushion
_Resting on the Couch with PHP_

## What is the Cushion?

Cushion is a PHP library meant solely for communicating with a CouchDB server.

## How do I use Cushion?

### Dependencies

Cushion requires the [PECL_HTTP](http://pecl.php.net/package/pecl_http) extension

#### Installing PECL_HTTP

	# pecl install pecl_http

### In Code

`cushion.class.php` is what you include in your PHP script, it pulls in the rest of the class files.

## Examples

### *C*reate a Document

	<?php

	require_once('../lib/cushion.class.php');

	try
	{
		# GET http://localhost:5984/test
		$db = Database::Select(new Server, 'test', true);

		# GET /test/_all_docs
		print_r($db->AllDocs());

		# POST /test/
		# {"foo":"foo","key":{"subkey":1,"subkey2":"value"}}
		$doc = $db->CreateDocument(Array(
			'foo' => 'foo',
			'key' => Array(
				'subkey' => 1,
				'subkey2' => 'value'
			)
		), 'test-document-1');

		// Output the document
		print_r($doc->doc);

		# GET /test/_all_docs
		print_r($db->AllDocs());
	}
	catch (CouchException $e)
	{
		echo $e->__toString();
	}

	?>

### *R*ead a Document

	<?php

	require_once('../cushion.class.php');

	try
	{
		# GET http://localhost:5984/test
		$db = Database::Select(new Server, 'test', true);

		# GET /test/test_doc_id
		$doc = $db->doc_read('test-document-1');

		// Output the document
		print_r($doc->doc);
	}
	catch (CouchException $e)
	{
		echo $e->__toString();
	}

	?>

### *U*pdate a Document

	<?php

	require_once('../lib/cushion.class.php');

	try
	{
		# GET http://localhost:5984/test
		$db = Database::Select(new Server, 'test', true);

		# GET /test/test_doc_id
		$doc = $db->GetDocument('test-document-1');

		// Output current document (note _rev)
		print_r($doc);

		// Change a value in the document
		$doc->doc['foo'] = 'bar123456789';

		# POST /mydb/test_doc_id
		$doc->Update();

		// Output new document (note _rev has changed)
		print_r($doc);
	}
	catch (CouchException $e)
	{
		echo $e->__toString();
	}

	?>

### *D*elete a Document

	<?php

	require_once('../lib/cushion.class.php');

	try
	{
		# GET http://localhost:5984/test
		$db = Database::Select(new Server, 'test', true);

		# GET /test/202086a8d868f65d6817e2ac342e7e9f
		$doc = $db->GetDocument('test-document-1');

		// Output the document itself
		print_r($doc->doc);

		# DELETE /test/202086a8d868f65d6817e2ac342e7e9f
		print_r($doc->Delete());
	}
	catch (CouchException $e)
	{
		echo $e->__toString();
	}

	?>