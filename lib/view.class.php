<?php

/**
 * Interface to retrieve the results of a view query
 *
 * @package cushion
 */
class View extends Cushion {
	public $Name;

	private $_designdoc;

	private $_methods;

	private $_active = false;

	public static function Add($designdoc, $name, $map, $reduce = null) {
		$v = new View;

		$v->_add($designdoc, $name, $map, $reduce);

		return $v;
	}

	public static function Get($designdoc, $name) {
		$v = new View;

		$v->_get($designdoc, $name);

		return $v;
	}

	public function Map($params = Array()) {
		if (isset($this->_methods['reduce']))
			$params['reduce'] = false;

		$this->_view_uri();

		return $this->_execute(null, null, null, $params);
	}

	public function Reduce($params = Array()) {
		$params['reduce'] = true;

		$this->_view_uri();

		return $this->_execute(null, null, null, $params);
	}

	public function ListQuery($listname, $params = null) {
		$this->_list_uri($listname);

		return $this->_execute(null, null, null, $params, null, false, false);
	}

	private function _add($designdoc, $name, $map, $reduce = null) {
		$this->_designdoc = $designdoc;
		$this->Name = $name;

		$this->_uri_pieces = $designdoc->_uri_pieces;
		$this->_view_uri();
		unset($this->_uri_pieces['query']['rev']);

		$this->_methods = array_filter(compact($map, $reduce));
		$designdoc->doc['views'][$name] = $this->_methods;
		$designdoc->Update();

		$this->_active = true;

		return $this->_execute();
	}

	private function _get($designdoc, $name) {
		$this->_designdoc = $designdoc;
		$this->Name = $name;

		$this->_uri_pieces = $designdoc->_uri_pieces;
		$this->_view_uri();
		unset($this->_uri_pieces['query']['rev']);

		if (isset($designdoc->doc['views'][$name]))
			$this->_methods = $designdoc->doc['views'][$name];
		else
			throw new Exception('View Not Found');

		$this->_active = true;
	}

	private function _update($designdoc, $name, $map, $reduce = null) {
		$this->_methods = array_filter(compact($map, $reduce));
		$designdoc->doc['views'][$name] = $this->_methods;
		$designdoc->Update();
	}

	private function _remove($designdoc, $name) {
		unset($this->_methods);

		unset($designdoc->doc['views'][$name]);
		$designdoc->Update();

		$this->_active = false;
	}

	private function _view_uri() {
		$this->_uri_pieces['path'][2] = '_view/' . $this->Name;
		unset($this->_uri_pieces['path'][3]);
	}

	private function _list_uri($listname) {
		$this->_uri_pieces['path'][2] = '_list/' . $listname;
		$this->_uri_pieces['path'][3] = $this->Name;
	}
}