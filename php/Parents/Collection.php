<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;

class Collection extends Data {
	
	protected $names = [];
	protected $indexes = [];
	protected $count;
	
	public function __construct() {
	}
	
	public function count() {
		return $this -> count;
	}
	
	public function getFirst() {
		return Objects::first($this -> data, 'value');
	}
	
	public function getLast() {
		return Objects::last($this -> data, 'value');
	}
	
	public function getFirstData() {
		$result = $this -> getFirst();
		//return $result['data'];
		//return $result -> getEntryData();
		return $result ? $result -> getEntryData() : null;
	}
	
	public function getLastData() {
		$result = $this -> getLast();
		//return $result['data'];
		//return $result -> getEntryData();
		return $result ? $result -> getEntryData() : null;
	}
	
	public function getNames() {
		return $this -> names;
	}
	
	public function getIndexes() {
		return $this -> indexes;
	}
	
	public function getFirstId() {
		$sorted = Objects::sort($this -> indexes);
		return Objects::first($sorted);
	}
	
	public function getLastId() {
		$sorted = Objects::sort($this -> indexes);
		return Objects::last($sorted, 'value');
	}
	
	public function getId($name) {
		return $this -> indexes[$name];
	}
	
	public function getName($id) {
		return Objects::find($this -> indexes, $id);
	}
	
	public function getById($id) {
		return $this -> getData($id);
	}
	
	public function getByName($name) {
		$id = $this -> getId($name);
		return $this -> getById($id);
	}
	
	public function getDataById($id) {
		$result = $this -> getById($id);
		//return $result['data'];
		return $result -> getEntryData();
	}
	
	public function getDataByName($name) {
		$result = $this -> getByName($name);
		//return $result['data'];
		return $result -> getEntryData();
	}
	
	public function add($data, $replace = true) {
		
		$id = $this -> getLastId();
		
		$new = new Entry;
		$new -> setEntry($data);
		
		$new -> setEntryKey('id', System::type($id, 'numeric') ? $id + 1 : 0);
		$new_id = $new -> getEntryKey('id');
		$new_name = $new -> getEntryKey('name');
		
		if (Objects::match($this -> names, $new_name)) {
			if ($replace) {
				$new_id = $this -> getId($new_name);
				$this -> data[$new_id] = $new;
			}
			return;
		}
		
		$this -> data[] = $new;
		$this -> names[] = $new_name;
		$this -> indexes[$new_name] = $new_id;
		$this -> count++;
		
		//$new = new Entry;
		//$new = Objects::merge( (array) $new, $data);
		//$new['id'] = System::type($id, 'numeric') ? $id + 1 : 0;
		//
		//if (Objects::match($this -> names, $new['name'])) {
		//	if ($replace) {
		//		$new['id'] = $this -> getId($new['name']);
		//		$this -> data[$new['id']] = $new;
		//	}
		//	return;
		//}
		//
		//$this -> data[] = $new;
		//$this -> names[] = $new['name'];
		//$this -> indexes[ $new['name'] ] = $new['id'];
		//$this -> count++;
		
	}
	
	public function addByList($data, $replace = true) {
		
		foreach ($data as $item) {
			$this -> add($item, $replace);
		}
		unset($item);
		
	}
	
	public function remove($id = null, $name = null) {
		
		$setId = System::set($id);
		$setName = System::set($name);
		
		if (!$setId && !$setName) {
			return;
		} elseif (!$setId) {
			$id = $this -> getId($name);
		} elseif (!$setName) {
			$name = $this -> getName($id);
		}
		
		$inname = Objects::find($this -> names, $name);
		
		unset($this -> data[$id]);
		unset($this -> names[$inname]);
		unset($this -> indexes[$name]);
		
		$this -> count--;
		
	}
	
	public function removeById($id) {
		$this -> remove($id, null);
	}
	
	public function removeByName($name) {
		$this -> remove(null, $name);
	}
	
	public function removeByList($data, $by) {
		
		if ($by === 'id') {
			foreach ($data as $item) {
				$this -> remove($item, null);
			}
			unset($item);
		} elseif ($by === 'name') {
			foreach ($data as $item) {
				$this -> remove(null, $item);
			}
			unset($item);
		}
		
	}
	
	public function removeByLen($skip, $len) {
		
		$this -> names = Objects::get($this -> names, $skip ? $skip : 0, $len ? $len : null);
		
		if ($by === 'name') {
			foreach ($data as $item) {
				$this -> remove(null, $item);
			}
			unset($item);
		}
		
	}
	
	public function removeByCut($skip, $len) {
		
		$names = Objects::cut($this -> names, $skip ? $skip : 0, $len ? $len : null);
		
		foreach ($names as $item) {
			$this -> remove(null, $item);
		}
		unset($item, $names);
		
	}
	
	public function removeFirst() {
		$id = $this -> getFirstId();
		$this -> remove($id['value'], null);
	}
	
	public function removeLast() {
		$id = $this -> getLastId();
		$this -> remove($id, null);
	}
	
	public function sortById() {
		$this -> names = Objects::keys($this -> indexes);
	}
	
	public function sortByName() {
		$this -> names = Objects::sort($this -> names);
	}
	
	public function sortByEntry($value) {
		$this -> names = [];
		foreach ($this -> data as $item) {
			if (System::typeClass($item, 'entry')) {
				$name = $item -> getEntryKey('name');
				$val = $item -> getEntryKey($value);
				$this -> names[$name] = System::typeOf($val, 'scalar') ? $val : '';
				unset($name, $val);
			}
		}
		unset($item);
		$this -> names = Objects::sort($this -> names);
		$this -> names = Objects::keys($this -> names);
	}
	
	public function sortByData($value) {
		$this -> names = [];
		foreach ($this -> data as $item) {
			if (System::typeClass($item, 'entry')) {
				$name = $item -> getEntryKey('name');
				$val = $item -> getEntryData($value);
				$this -> names[$name] = System::typeOf($val, 'scalar') ? $val : '';
				unset($name, $val);
			}
		}
		unset($item);
		$this -> names = Objects::sort($this -> names);
		$this -> names = Objects::keys($this -> names);
	}
	
	public function reverse() {
		$this -> names = Objects::reverse($this -> names);
	}
	
	public function randomize() {
		$this -> names = Objects::randomize($this -> names);
	}
	
	public function reset() {
		$this -> data = [];
		$this -> names = [];
		$this -> indexes = [];
		$this -> count = 0;
	}
	
	public function iterate($callback) {
		Objects::each($this -> getNames(), function($name, $key, $position) use ($callback) {
			call_user_func($callback, $this -> getByName($name), $key, $position);
		});
	}
	
}

?>