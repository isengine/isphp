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
	
	public $route;
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
	
	public function parseStructure($array = null, $level = 0, $parents = [], $groups = null) {
		
		$parents_string = $parents ? Strings::join($parents, '/') . '/' : null;
		
		foreach ($array as $key => $item) {
			
			$i = Objects::fill(
				['name', 'type', 'template'],
				Parser::fromString($key)
			);
			
			$name = $i['name'];
			
			if ($parents) {
				$i['name'] = Strings::join($parents, ':') . ':' . $name;
				$i['parents'] = $parents;
			}
			
			$i['data'] = [
				'name' => $name,
				'groups' => $groups,
				'template' => $i['template'],
				'level' => $level,
				'sub' => System::typeOf($item, 'iterable'),
				'link' => $i['type'] === 'group' ? null : (System::typeOf($item, 'scalar') ? $item : $parents_string . $name . '/')
			];
			
			if (
				$i['type'] !== 'group' &&
				$i['type'] !== 'special'
			) {
				$i['data']['link'] = Paths::relativeUrl($i['data']['link']);
			}
			
			unset($i['template']);
			$this -> structure -> add($i);
			
			if (System::typeOf($item, 'iterable')) {
				
				if ($i['type'] === 'group') {
					$groups[] = $name;
				} else {
					$level++;
					$parents[] = $name;
				}
				
				$this -> parseStructure($item, $level, $parents, $groups);
				
				if ($i['type'] === 'group') {
					$groups = Objects::unlast($groups);
				} else {
					$level--;
					$parents = Objects::unlast($parents);
				}
				
			}
			
			//echo '<pre>' . print_r($i, 1) . '</pre>';
			
		}
		unset($key, $item);
		
		
	}
	
	
}

?>