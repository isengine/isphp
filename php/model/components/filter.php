<?php

namespace is\Model\Components;

use is\Model\Parents\Data;
use is\Helpers\Local;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\System;
use is\Helpers\Prepare;
use is\Helpers\Match;

class Filter extends Data {
	
	public $method; // параметры фильтрации результата из базы данных
	public $result; // результат работы фильтра
	
	// data - это параметры фильтрации результата
	
	public function __construct() {
		$this -> resetFilter();
	}
	
	public function methodFilter($name) {
		$this -> method = $name;
	}
	
	public function clearFilter() {
		$this -> resetData();
	}
	
	public function resetFilter() {
		$this -> clearFilter();
		$this -> resetResult();
		$this -> methodFilter('and');
	}
	
	public function addFilter($name = null, $data = null) {
		
		if (is_array($name)) {
			$this -> addData($name);
		} else {
			
			$item = [
				'name' => null,
				'data' => null,
				'values' => []
			];
			
			$array = Parser::fromString($name);
			$item['name'] = Objects::first($array, 'value');
			if ($item['name'] === 'data') {
				$item['name'] = Objects::n($array, 1, 'value');
				$item['data'] = true;
			}
			unset($array);
			
			if ($data) {
				$array = Parser::fromString($data);
				foreach ($array as $i) {
					
					$value = [
						'name' => null,
						'type' => 'equal',
						'require' => null,
						'except' => null
					];
					
					$first = Strings::first($i);
					$num = Strings::match($i, '_');
					
					if (
						$first === '+' ||
						$first === '-'
					) {
						$value['except'] = $first === '-' ? true : null;
						$value['require'] = true;
						$i = Strings::unfirst($i);
						$value['name'] = $i;
						$first = Strings::first($i);
					}
					
					if ($first === '*') {
						$value['type'] = 'string';
						$value['name'] = Strings::unfirst($i);
					} elseif ($num) {
						$value['type'] = 'numeric';
						$value['name'] = Strings::split($i, '_');
						foreach ($value['name'] as &$ii) {
							if ($ii) {
								$ii = Prepare::numeric($ii);
							}
						}
						unset($ii);
					} else {
						$value['name'] = $i;
					}
					
					$item['values'][] = $value;
					
					unset($value);
					
				}
				unset($i, $array);
			} else {
				$item['values'][] = ['type' => 'noempty'];
			}
			
			$this -> addData($item);
			
			unset($item);
			
		}
		
	}
	
	public function getResult() {
		return $this -> result;
	}
	
	public function resetResult() {
		$this -> result = [];
	}
	
	public function filtrationByList($data, $keys = null) {
		if (!System::typeOf($keys, 'iterable')) {
			$keys = null;
		}
		foreach ($data as $key => $item) {
			if ($this -> filtration($item)) {
				$this -> result[] = $keys ? $keys[$key] : $key;
			}
		}
		unset($item);
	}
	
	public function filtration($entry) {
		
		if (!$entry || !$this -> data) {
			return true;
		}
		
		$and = $this -> method === 'and';
		
		$tpass = $and;
		
		foreach ($this -> data as $key => $item) {
			
			if ($item['data']) {
				$data = $entry['data'][$item['name']];
			} else {
				$i = $item['name'];
				$data = System::type($entry, 'object') ? $entry -> $i : $entry[$i];
				unset($i);
			}
			
			$gpass = null;
			
			foreach ($item['values'] as $i) {
				
				//if ($i['type'] === 'noempty') {
				//	$pass = System::set($data);
				//} elseif ($i['type'] === 'equal') {
				//	$pass = is_array($data) ? Match::equalIn($data, $i['name'], null) : Match::equal($data, $i['name']);
				//} elseif ($i['type'] === 'string') {
				//	$pass = is_array($data) ? Match::stringIn($data, $i['name'], null) : Match::string($data, $i['name']);
				//} elseif ($i['type'] === 'numeric') {
				//	$pass = is_array($data) ? Match::numericIn($data, $i['name'][0], $i['name'][1], null) : Match::numeric($data, $i['name']);
				//}
				
				if ($i['type'] === 'noempty') {
					$pass = System::set($data);
				} else {
					$func = $i['type'] . (is_array($data) ? 'In' : null);
					if ($i['type'] === 'numeric') {
						$pass = Match::$func($data, $i['name'][0], $i['name'][1], null);
					} else {
						$pass = Match::$func($data, $i['name'], null);
					}
					unset($func);
				}
				
				if ($i['except']) {
					$pass = !$pass;
				}
				
				if ($i['require'] && !$pass) {
					$gpass = null;
					break;
				}
				
				if ($pass || $gpass) {
					$gpass = true;
				}
				
				unset($pass);
				//echo '<pre>I:' . print_r($i, 1) . '</pre>';
				
			}
			unset($i);
			
			if ($and && $gpass && $tpass) {
				$tpass = true;
			} elseif (!$and && ($gpass || $tpass)) {
				$tpass = true;
			} else {
				$tpass = null;
			}
			
			unset($gpass);
			
			//echo '<pre>' . print_r($entry, 1) . '</pre>';
			//echo '<pre>' . print_r($item, 1) . '</pre>';
			//echo '<pre>DATA:' . print_r($data, 1) . '</pre>';
			//echo '<pre>RESULT:' . print_r($tpass, 1) . '</pre>';
			//echo '<hr>';
			
		}
		unset($key, $item);
		
		//echo '<pre>RESULT:' . print_r($tpass, 1) . '</pre>';
		// если вернуть пустое значение, то текущая запись не внесется в общий лист
		
		return $tpass;
		
	}
	
}

?>