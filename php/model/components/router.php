<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Paths;
use is\Model\Globals;

class Router extends Globals\Router {
	
	public $home;
	public $route;
	public $current;
	public $template;
	
	public function init() {
		
		$this -> structure = new Collection;
		
		//$this -> structure = [];
		//$this -> data = new Collection;
		
	}
	
	public function setHome() {
		$this -> structure -> addFilter('type', 'home');
		$home = $this -> structure -> filterByList();
		$this -> structure -> resetFilter();
		$this -> home = Objects::first($home, 'value');
	}
	
	public function getHome() {
		return $this -> home;
	}
	
	public function setStructure($data) {
		$this -> structure -> addByList($data);
	}
	
	public function getStructure() {
		return $this -> structure -> getData();
	}
	
	public function parseStructure($array = null, $level = 0, $parents = []) {
		
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
				'template' => $i['template'],
				'level' => $level,
				'sub' => System::typeOf($item, 'iterable'),
				'link' => Paths::prepareUrl(System::typeOf($item, 'scalar') ? $item : $parents_string . $name . '/')
			];
			
			if ($i['type'] === 'home') {
				$i['data']['link'] = null;
			} elseif (System::typeOf($item, 'scalar')) {
				$i['data']['link'] = $item;
			} else {
				$i['data']['link'] = $parents_string . $name . '/';
			}
						$i['data']['link'] = Paths::prepareUrl($i['data']['link']);
			
			unset($i['template']);
			$this -> structure -> add($i);
			
			if (System::typeOf($item, 'iterable')) {
				$level++;
				$parents[] = $name;
				
				$this -> parseStructure($item, $level, $parents);
				
				$level--;
				$parents = Objects::unlast($parents);
			}
			
			//echo '<pre>' . print_r($i, 1) . '</pre>';
			
		}
		unset($key, $item);
		
		
	}
	
	
}

?>