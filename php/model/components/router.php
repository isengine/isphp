<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Paths;
use is\Model\Parents\Globals;

class Router extends Globals {
	
	public $structure;
	public $current;
	public $template;
	
	public function init() {
		
		$this -> structure = new Collection;
		
		$this -> template = [
			'name' => null,
			'section' => null
		];
		
	}
	
	public function setStructure($data) {
		$this -> structure -> addByList($data);
	}
	
	public function getStructure() {
		return $this -> structure -> getData();
	}
	
	public function parseStructure($array = null, $level = 0, $parents = [], $groups = null, $cache = null) {
		
		$parents_string = $parents ? Strings::join($parents, '/') . '/' : null;
		
		foreach ($array as $key => $item) {
			
			$k = Parser::fromString($key, ['simple' => null]);
			
			$type = System::typeOf($k[0], 'scalar');
			$name = $type ? $k[0] : $k[0][0];
			
			$i = [
				'name' => $parents ? Strings::join($parents, ':') . ':' . $name : $name,
				'type' => $type ? null : $k[0][1],
				'parents' => $parents ? $parents : null,
				'data' => [
					'name' => $name,
					'groups' => $groups,
					'template' => $k[1][0],
					'cache' => [
						'page' => $k[2],
						'browser' => $k[3]
					],
					'level' => $level,
					'sub' => System::typeOf($item, 'iterable'),
					'link' => $i['type'] === 'group' ? null : (System::typeOf($item, 'scalar') ? $item : $parents_string . $name . '/')
				]
			];
			
			foreach (['page', 'browser'] as $ii) {
				$n = &$i['data']['cache'][$ii];
				if ($n) {
					if (Objects::len($n) === 1) {
						$n = Objects::first($n, 'value');
					}
					if ($n === 'parent') {
						$n = $cache[$ii];
					}
					if ($n) {
						$cache[$ii] = $n;
					}
				}
				unset($n);
			}
			unset($ii);
			
			if (
				$i['type'] !== 'group' &&
				$i['type'] !== 'special'
			) {
				$i['data']['link'] = Paths::prepareUrl($i['data']['link']);
			}
			
			$this -> structure -> add($i);
			//echo '<pre>' . print_r($i, 1) . '</pre>';
			
			if (System::typeOf($item, 'iterable')) {
				
				if ($i['type'] === 'group') {
					$groups[] = $name;
				} else {
					$level++;
					$parents[] = $name;
				}
				
				$this -> parseStructure($item, $level, $parents, $groups, $cache);
				
				if ($i['type'] === 'group') {
					$groups = Objects::unlast($groups);
				} else {
					$level--;
					$parents = Objects::unlast($parents);
				}
				
			}
			
		}
		unset($key, $item);
		
	}
	
	
}

?>