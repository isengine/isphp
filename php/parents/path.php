<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Strings;

class Path extends Data {
	
	public $url;
	public $real;
	
	public function __construct($path = __DIR__ . DS) {
		$this -> setPath($path);
	}
	
	public function setPath($path = __DIR__ . DS) {
		
		if (
			Strings::find($path, '.') !== false &&
			Strings::find($path, '.') < 2
		) {
			$this -> real = realpath($path);
			$this -> setUrl();
		} elseif (!$path) {
			$this -> real = realpath($path);
			$this -> setUrl();
		} elseif (
			Strings::find($path, 'http') === 0 ||
			Strings::match($path, '//') ||
			!Strings::match($path, DS)
		) {
			$this -> url = $path;
			$this -> setReal();
		} else {
			$this -> real = realpath($path);
			$this -> setUrl();
		}
		
	}
	
	public function control() {
		if (!$this -> url) {
			$this -> url = '/';
		}
		$root = str_replace(['/', '\\'], DS, $_SERVER['DOCUMENT_ROOT']) . DS;
		if (Strings::find($this -> real, $root) === false) {
			$this -> real = $root;
		}
	}
	
	public function setUrl() {
		$this -> url = mb_substr(str_replace(DS, '/', $this -> real), mb_strlen($_SERVER['DOCUMENT_ROOT']));
		$this -> control();
	}
	
	public function setReal() {
		$path = $this -> url;
		$len = mb_strlen($_SERVER['HTTP_HOST']);
		$pos = mb_strpos($path, $_SERVER['HTTP_HOST']);
		if ($pos === false) {
			$pos = mb_strpos($path, '//');
		}
		$result = $_SERVER['DOCUMENT_ROOT'] . mb_substr($path, $pos !== false ? $pos + $len : $pos);
		$this -> real = str_replace(['/', '\\'], DS, $result);
		$this -> control();
	}
	
	public function include() {
		if (System::typeData($this -> data)) {
			
			$this -> eachData($a, function($item){
				$item = str_replace(['..','\/','\\',':'], ['','','',DS], $item);
				$item = $this -> real . $item . '.php';
				
				if (file_exists($item)) {
					require_once $item;
				}
			});
			
		}
	}
	
}

?>