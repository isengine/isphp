<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Parents;

class Collection extends Parents\Collection {
	
	public $filter;
	
	public function __construct() {
		$this -> filter = new Filter;
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
	
	public function filterByList() {
		$list = Objects::flip($this -> getIndexes());
		$this -> filter -> resetResult();
		$this -> filter -> filtrationByList($this -> data, $list);
		return $this -> filter -> getResult();
	}
	
}

?>