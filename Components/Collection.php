<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Parents;

class Collection extends Parents\Collection {
	
	public $filter;
	public $map;
	
	public function __construct() {
		$this -> filter = new Filter;
		$this -> map = new Parents\Map;
	}
	
	public function addFilter($name = null, $data = null) {
		$this -> filter -> addFilter($name, $data);
	}
	
	public function methodFilter($name) {
		$this -> filter -> methodFilter($name);
	}
	
	public function resetFilter() {
		$this -> filter -> resetFilter();
		$this -> filter -> resetResult();
	}
	
	public function filtration() {
		$list = Objects::flip($this -> getIndexes());
		$this -> filter -> resetResult();
		$this -> filter -> filtrationByList($this -> data, $list);
		$this -> names = $this -> filter -> getResult();
	}
	
	public function dataMap($from = null) {
		$this -> map -> reset();
		foreach ($this -> names as $item) {
			$data = null;
			if ($from) {
				$data = $this -> getDataByName($item);
				$data = Objects::extract($data, Strings::split($from, ':'));
			}
			$this -> map -> addMap($item, $data);
			unset($data);
		}
		unset($item);
	}
	
	public function countMap($tags = null) {
		return $this -> map -> count($this -> names, $tags);
	}
	
	public function getMapById($id) {
		$name = $this -> getName($id);
		return $this -> map -> getMap($name);
	}
	
	public function getMapByName($name) {
		return $this -> map -> getMap($name);
	}
	
	public function add($data, $replace = true) {
		$name = parent::add($data, $replace);
		$this -> map -> addMap($name);
	}
	
	public function remove($id = null, $name = null) {
		$name = parent::remove($id, $name);
		$this -> map -> removeMap($name);
	}
	
	public function reset() {
		parent::reset();
		$this -> map -> reset();
	}
	
}

?>