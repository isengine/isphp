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
	
	public function refresh() {
		$this -> sortById();
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
		$new_parents = $new -> getEntryKey('parents');
		$new_name = (System::typeIterable($new_parents) ? Strings::join($new_parents, ':') . ':' : null) . $new -> getEntryKey('name');
		
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
		
		return $new_name;
		
		//System::debug(Strings::split($new_name, ':'), '!q');
		
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
		
		return $name;
		
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
				$parents = $item -> getEntryKey('parents');
				$name = (System::typeIterable($parents) ? Strings::join($parents, ':') . ':' : null) . $item -> getEntryKey('name');
				$val = $item -> getEntryKey($value);
				$this -> names[$name] = System::typeOf($val, 'scalar') ? $val : '';
				unset($parents, $name, $val);
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
				$parents = $item -> getEntryKey('parents');
				$name = (System::typeIterable($parents) ? Strings::join($parents, ':') . ':' : null) . $item -> getEntryKey('name');
				$val = $item -> getEntryData($value);
				$this -> names[$name] = System::typeOf($val, 'scalar') ? $val : '';
				unset($parents, $name, $val);
			}
		}
		unset($item);
		$this -> names = Objects::sort($this -> names);
		$this -> names = Objects::keys($this -> names);
	}
	
	public function leaveById($id) {
		$current = $this -> getById($id);
		$this -> reset();
		$this -> add($current);
		unset($current);
	}
	
	public function leaveByName($name) {
		$current = $this -> getByName($name);
		$this -> reset();
		$this -> add($current);
		unset($current);
	}
	
	public function leaveByList($data, $by) {
		
		$list = $this -> indexes;
		
		if ($by === 'id') {
			$list = Objects::values($list);
		} elseif ($by === 'name') {
			$list = Objects::keys($list);
		} else {
			return;
		}
		
		$diff = array_diff($list, $data);
		$this -> removeByList($diff, $by);
		
	}
	
	public function reverse() {
		$this -> names = Objects::reverse($this -> names);
	}
	
	public function random() {
		$this -> names = Objects::random($this -> names);
	}
	
	public function reset() {
		$this -> data = [];
		$this -> names = [];
		$this -> indexes = [];
		$this -> count = 0;
	}
	
	public function iterate($callback, $limit = null) {
		$iterate = System::set($limit);
		Objects::each($this -> getNames(), function($name, $key, $position) use ($callback, $limit, $iterate) {
			call_user_func($callback, $this -> getByName($name), $key, $position);
			if ($iterate) {
				$limit--;
				if ($limit <= 0) {
					//break;
					return;
				}
			}
		});
	}
	
}

?>