<?php

namespace is\Masters\Modules;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;
use is\Helpers\Prepare;
use is\Helpers\Paths;

use is\Components\Dom;

use is\Parents\Data;

abstract class Master extends Data {
	
	public $instance; // имя экземпляра модуля
	public $template; // шаблон экземпляра модуля
	public $settings; // настройки
	public $path; // путь до папки модуля
	public $custom_path; // путь до кастомной папки модуля в app
	
	public $elements; // группа элементов модуля
	
	public function __construct(
		$instance,
		$template,
		$settings,
		$path,
		$custom
	) {
		
		$this -> instance = $instance;
		$this -> template = $template;
		$this -> path = $path;
		$this -> custom_path = $custom;
		
		$this -> settings = $settings;
		//$this -> settings = new Data;
		//$this -> settings -> setData($settings);
		
		$this -> elements();
		$this -> classes();
		
		//$this -> launch();
		
	}
	
	abstract public function launch();
	
	public function block($name, $item = null) {
		
		$path = [
			$this -> custom_path,
			$this -> path
		];
		
		foreach ($path as $i) {
			$i = Paths::toReal($i . DS . 'blocks' . DS . $name . '.php');
			if (Local::matchFile($i)) {
				require($i);
				break;
			}
		}
		unset($i);
		
		//if ( !System::includes($name, $this -> custom . 'blocks', null, $object ? $object : $this) ) {
		//	System::includes($name, $this -> path . 'blocks', null, $object ? $object : $this);
		//}
		
	}
	
	public function template() {
		
		$path = [
			$this -> custom_path . 'templates' . DS . $this -> template,
			$this -> path . 'templates' . DS . $this -> template,
			$this -> custom_path . 'templates' . DS . 'default',
			$this -> path . 'templates' . DS . 'default'
		];
		
		foreach ($path as $i) {
			$i = Paths::toReal($i . '.php');
			if (Local::matchFile($i)) {
				require($i);
				break;
			}
		}
		unset($i);
		
	}
	
	public function elements() {
		
		unset($this -> elements);
		$this -> elements = [];
		
		if ( !System::typeIterable($this -> settings['elements']) ) {
			return;
		}
		
		foreach ($this -> settings['elements'] as $key => $item) {
			if ($item) {
				$this -> elements[$key] = new Dom($item);
			}
		}
		unset($key, $item);
		
	}
	
	public function classes() {
		
		if (
			!System::typeIterable($this -> elements) ||
			!System::typeIterable($this -> settings['classes'])
		) {
			return;
		}
		
		foreach ($this -> settings['classes'] as $key => $item) {
			if ($item && System::typeClass($this -> elements[$key], 'dom')) {
				$this -> elements[$key] -> addClass($item);
			}
		}
		unset($key, $item);
		
	}
	
	public function eget($element) {
		return $this -> elements[$element];
	}
	
	public function ecopy($from, $to) {
		$this -> elements[$to] = $this -> elements[$from] -> copy();
	}
	
	public function ecreate($name, $tag) {
		$this -> elements[$name] = new Dom($tag);
	}
	
}

?>