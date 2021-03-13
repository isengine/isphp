<?php

namespace is\Model\Parents;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Match;

class Collection extends Data {
	
	protected $names = [];
	protected $indexes = [];
	protected $count;
	
	public function __construct($data = null) {
		if (System::typeData($data, 'object')) {
			$this -> data = $data;
			$this -> init();
		}
	}
	
	public function __clone() {
		// это надо пробовать, возможно, лучше создать отдельный объект, хранящий свойства коллекции
		unset($this -> data);
	}
	
	public function init() {
		
		// обновляем счетчик
		
		$this -> count = Objects::len($this -> data);
		
		// обновляем список имен
		
		$this -> names = [];
		$this -> indexes = [];
		foreach ($this -> data as $key => $item) {
			$this -> names[] = $item['name'];
			$this -> indexes[ $item['name'] ] = $key;
		}
		unset($key, $item);
		
	}
	
	public function count() {
		return $this -> count;
	}
	
	public function get() {
		return $this -> data;
	}
	
	public function getFirst() {
		return Objects::first($this -> data, 'value');
	}
	
	public function getLast() {
		return Objects::last($this -> data, 'value');
	}
	
	public function getFirstData() {
		$result = $this -> getFirst();
		return $result['data'];
	}
	
	public function getLastData() {
		$result = $this -> getLast();
		return $result['data'];
	}
	
	public function getNames() {
		return $this -> names;
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
		return $result['data'];
	}
	
	public function getDataByName($name) {
		$result = $this -> getByName($name);
		return $result['data'];
	}
	
	public function add($data, $replace = true) {
		
		$id = $this -> getLastId();
		
		$new = new Entry;
		$new = Objects::merge( (array) $new, $data);
		$new['id'] = System::type($id, 'numeric') ? $id + 1 : 0;
		
		if (Objects::match($this -> names, $new['name'])) {
			if ($replace) {
				$new['id'] = $this -> getId($new['name']);
				$this -> data[$new['id']] = $new;
			}
			return;
		}
		
		$this -> data[] = $new;
		$this -> names[] = $new['name'];
		$this -> indexes[ $new['name'] ] = $new['id'];
		$this -> count++;
		
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
			if (System::typeData($item, 'object')) {
				$this -> names[ $item['name'] ] = System::typeOf($item[$value], 'scalar') ? $item[$value] : '';
			}
		}
		unset($item);
		$this -> names = Objects::sort($this -> names);
		$this -> names = Objects::keys($this -> names);
	}
	
	public function sortByData($value) {
		$this -> names = [];
		foreach ($this -> data as $item) {
			if (System::typeData($item['data'], 'object')) {
				$this -> names[ $item['name'] ] = System::typeOf($item['data'][$value], 'scalar') ? $item['data'][$value] : '';
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
	
}

?>