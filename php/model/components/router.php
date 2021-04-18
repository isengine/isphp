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
			
			$type = System::typeOf($k[0], 'scalar') ? null : $k[0][1];
			$name = System::typeOf($k[0], 'scalar') ? $k[0] : $k[0][0];
			
			$i = [
				'name' => $parents ? Strings::join($parents, ':') . ':' . $name : $name,
				'type' => $type,
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
					'link' => $type === 'group' ? null : (System::typeOf($item, 'scalar') ? $item : $parents_string . $name . '/')
				]
			];
			
			// special позволяет не делать обработку урла, что нужно для использования #... или ?..=..&..=..
			// чтобы убрать special нужно сделать более правильную обработку урла через helper
			// Paths::prepareUrl сейчас с этим не справляется, он не учитывает этот синтаксис
			
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
				$type !== 'group' &&
				$type !== 'special'
			) {
				$i['data']['link'] = Paths::prepareUrl($i['data']['link']);
			}
			
			if ($type !== 'group') {
				$this -> structure -> add($i);
			}
			//echo '<pre>' . print_r($i, 1) . '</pre>';
			
			if (System::typeOf($item, 'iterable')) {
				
				if ($type === 'group') {
					$groups[] = $name;
				} else {
					$level++;
					$parents[] = $name;
				}
				
				$this -> parseStructure($item, $level, $parents, $groups, $cache);
				
				if ($type === 'group') {
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