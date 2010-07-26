<?php

class DesignDocument extends Document {
	public $Name;

	public static function Create($db, $designdoc_name, $views, $shows = null, $lists = null, $updates = null, $validate = null) {
		$d = new DesignDocument;

		$doc = Array('views' => $views);
		if (isset($shows))	$doc['shows'] = $shows;
		if (isset($lists))	$doc['lists'] = $shows;
		if (isset($updates))	$doc['updates'] = $updates;
		if (isset($validate))	$doc['validate_doc_update'] = $validate;

		$d->_create($db, $doc, '_design/' . $designdoc_name);
		$d->Name = $designdoc_name;

		return $d;
	}

	public static function Get($db, $designdoc_name, $rev = null) {
		$d = new DesignDocument;

		$d->_get($db, '_design/' . $designdoc_name, $rev);
		$d->Name = $designdoc_name;
		$d->_active = true;

		return $d;
	}

	public function GetView($view_name) {
		return View::Get($this, $view_name);
	}

	public function MapView($name, $params = null) {
		return View::Get($this, $name)->Map($params);
	}

	public function ReduceView($name, $params = null) {
		return View::Get($this, $name)->Reduce($params);
	}
}