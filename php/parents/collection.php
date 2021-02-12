<?php

namespace is\Parents;

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
		return $this -> names;
	}
	
	public function getFirst() {
		$sorted = Objects::sort($this -> indexes);
		return Objects::first($sorted);
	}
	
	public function getLast() {
		$sorted = Objects::sort($this -> indexes);
		return Objects::last($sorted);
	}
	
	public function getId($name) {
		return $this -> indexes[$name];
	}
	
	public function getName($id) {
		return Objects::find($this -> indexes, $id);
	}
	
	public function add($data) {
		
		$id = $this -> getLast();
		
		$new = new Entry;
		$new = Objects::merge( (array) $new, $data);
		$new['id'] = $id['value'] + 1;
		
		$this -> data[] = $new;
		$this -> names[] = $new['name'];
		$this -> indexes[ $new['name'] ] = $new['id'];
		$this -> count++;
		
	}
	
	public function addByList($data) {
		
		foreach ($data as $item) {
			$this -> add($item);
		}
		unset($item);
		
	}
	
	public function remove($id = null, $name = null) {
		
		$setId = System::set($id);
		$setName = System::set($name);
		
		if (!$setId && !$setId) {
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
		$id = $this -> getFirst();
		$this -> remove($id['value'], null);
	}
	
	public function removeLast() {
		$id = $this -> getLast();
		$this -> remove($id['value'], null);
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
	
	public function filter($parameters = null) {
		
		/*
		*  ФУНКЦИЮ НАДО ПЕРЕПИЛИТЬ ИЛИ УДАЛИТЬ ВООБЩЕ
		*  перепил заключается в том, чтобы сделать ее возвратом массива ключей и значений уже отфильтрованных
		*  0 - type: by entry / by data
		*  1 - value: name entry/data filed
		*  2 - match name
		*  ./0 - haystack (add)
		*  3/1 - needle / minmax / min
		*  4/2 - and / max
		*  5/3 - ... / and
		*  6 - sort
		*/
		
		$type = array_shift($parameters);
		$value = array_shift($parameters);
		$name = array_shift($parameters);
		$sort = $parameters[6];
		
		$this -> names = [];
		foreach ($this -> data as $item) {
			
			$setType = $type === 'entry' ? $item : $item['data'];
			$setValue = $type === 'entry' ? $item[$value] : $item['data'][$value];
			
			if (
				System::typeData($setType, 'object') &&
				System::typeOf($setValue, 'scalar') &&
				Match::common($name, array_merge($setValue, $parameters))
			) {
				$this -> names[ $item['name'] ] = System::typeOf($setValue, 'scalar') ? $setValue : '';
			}
			
		}
		unset($item);
		
		if ($sort) {
			$this -> names = Objects::sort($this -> names);
		}
		
		$this -> names = Objects::keys($this -> names);
	}
	
}

?>