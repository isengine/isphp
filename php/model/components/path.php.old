<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Paths;
use is\Model\Parents\Data;

class Path extends Data {
	
	public $url;
	public $real;
	
	public $parse;
	
	private $root;
	private $host;
	
	public function __construct($path = null, $root = null, $host = null) {
		
		$this -> root = $root ? $root : DR;
		$this -> host = $host ? $host : str_replace(['/', '\\'], DS, $_SERVER['DOCUMENT_ROOT']) . DS;
		unset($root, $host);
		
		if ($path) {
			
			$ptemp = '//' . $_SERVER['HTTP_HOST'];
			$plen = Strings::len($ptemp);
			$ppos = Strings::find($path, $ptemp);
			if ($plen && $ppos !== false) {
				$path = mb_substr($this -> host, Strings::len($this -> root), -1) . mb_substr($path, $ppos + $plen);
			}
			unset($ptemp, $plen, $ppos);
			
			$path = Paths::clearSlashes($path);
			if (Strings::find($path, $this -> root) !== 0) {
				$path = Paths::convertToReal($path);
			}
		}
		
		if (!$path) {
			$this -> reset();
		} else {
			$this -> setPathReal($path);
		}
		
	}
	
	public function setPathReal($path) {
		if (Strings::find($path, $this -> root) === 0) {
			$this -> real = $path . DS;
		} else {
			$this -> real = realpath($this -> root . $path) . DS;
		}
		$this -> setUrl();
	}
	
	public function setPathUrl($path) {
		$this -> url = $path . '/';
		$this -> setReal();
	}
	
	public function reset() {
		$this -> real = null;
		$this -> url = null;
		$this -> update();
	}
	
	private function update() {
		if (!$this -> url) {
			$this -> url = '/';
		}
		if (Strings::find($this -> real, $this -> root) === false) {
			$this -> real = $this -> root;
		}
	}
	
	public function setUrl() {
		if (Strings::find($this -> real, $this -> host) === false) {
			$this -> url = '/';
		} else {
			$this -> url = mb_substr(str_replace(DS, '/', $this -> real), mb_strlen($this -> host) - 1);
		}
		$this -> update();
	}
	
	public function setReal() {
		$path = $this -> url;
		$len = mb_strlen($_SERVER['HTTP_HOST']);
		$pos = mb_strpos($path, $_SERVER['HTTP_HOST']);
		if ($pos === false) {
			$pos = mb_strpos($path, '//');
		}
		$result = mb_substr($this -> host, 0, -1) . mb_substr($path, $pos !== false ? $pos + $len : $pos);
		$this -> real = str_replace(['/', '\\'], DS, $result);
		$this -> update();
	}
	
	public function getUrl($path) {
		
		$real = Paths::convertToReal($path);
		$real = $this -> real . $real;
		
		if (file_exists($real)) {
			$item = Paths::convertToUrl($path);
			$item = $this -> url . $item;
			return $item;
		}
		
	}
	
	public function getReal($path) {
		
		$item = Paths::convertToReal($path);
		$item = $this -> real . $item;
		
		if (file_exists($item)) {
			return $item;
		}
		
	}
	
	 public function parseUrl(){
		
		// Функция парсинга url-адреса
		
		$this -> parse = Paths::parseUrl($this -> url);
		
		echo print_r($this -> parse, 1);
		
	}
	
	public function include($path = null, $return = null) {
		
		$data = null;
		
		if (System::set($path)) {
			
			$item = Paths::convertToReal($path);
			$item = $this -> real . $item . '.php';
			unset($path);
			
			if (file_exists($item)) {
				require_once $item;
			}
			
		} elseif (System::typeData($this -> data)) {
			
			unset($path);
			
			$this -> eachData($a, function($item){
				$item = Paths::convertToReal($item);
				$item = $this -> real . $item . '.php';
				
				if (file_exists($item)) {
					require_once $item;
				}
			});
			
		}
		
		if ($return) {
			return $$return;
		}
		
	}
	
	public function print($path = null) {
		if (System::set($path)) {
			
			$real = Paths::convertToReal($path);
			$real = $this -> real . $item;
			unset($path);
			
			if (file_exists($real)) {
				$item = Paths::convertToUrl($path);
				$item = $this -> url . $item;
				echo $item;
			}
			
		} elseif (System::typeData($this -> data)) {
			
			$this -> eachData($a, function($path){
				
				$real = Paths::convertToReal($path);
				$real = $this -> real . $item;
				
				if (file_exists($real)) {
					$item = Paths::convertToUrl($path);
					$item = $this -> url . $item;
					unset($path, $real);
					echo $item;
				}
				
			});
			
		}
	}
	
}

?>