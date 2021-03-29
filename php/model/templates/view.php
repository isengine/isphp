<?php

namespace is\Model\Templates;

use is\Model\Parents\Data;
use is\Helpers\Local;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\System;
use is\Helpers\Prepare;
use is\Helpers\Match;
use is\Model\Components\Language;
use is\Model\Components\Router;
use is\Model\Components\Uri;

abstract class View extends Data {
	
	/*
	это фактически интерфейс вида
	*/
	
	public $path;
	public $lang;
	public $router;
	public $uri;
	
	public function __construct($path = null) {
		$this -> init($path);
	}
	
	abstract public function includes();
	
	public function init($path = null) {
		$this -> lang = Language::getInstance();
		$this -> router = Router::getInstance();
		$this -> uri = Uri::getInstance();
		if ($path) {
			$this -> setRealPath($path);
		}
	}
	
	// группа работы с языком
	
	public function langName() {
		return $this -> lang -> lang;
	}
	
	public function langCode($lang = null) {
		if (!$lang) {
			return $this -> lang -> code;
		} else {
			$name = $this -> lang -> list[$lang];
			return $this -> lang -> codes[$name];
		}
	}
	
	public function langList() {
		return Objects::keys($this -> lang -> settings);
	}
	
	public function langListAll() {
		return $this -> lang -> list;
	}
	
	public function lang($data) {
		$data = Parser::fromString($data);
		$array = $this -> lang -> getData();
		return Objects::extract($array, $data);
	}
	
	// группа работы с uri
	
	public function uri() {
		return $this -> uri -> url;
	}
	
	public function scheme() {
		return $this -> uri -> scheme;
	}
	
	public function host() {
		return $this -> uri -> host;
	}
	
	public function domain() {
		return $this -> uri -> domain;
	}
	
	public function previous() {
		return $this -> uri -> previous;
	}
	
	public function uriPath() {
		return $this -> uri -> path['string'];
	}
	
	public function uriPathArray() {
		return $this -> uri -> path['array'];
	}
	
	public function query() {
		return $this -> uri -> query['string'];
	}
	
	public function queryArray() {
		return $this -> uri -> query['array'];
	}
	
	public function data() {
		return $this -> uri -> data;
	}
	
	// группа работы с абсолютным путем
	
	public function setRealPath($path) {
		$this -> path = $path;
	}
	
	public function getRealPath() {
		return $this -> path;
	}
	
	public function setRealCache($path) {
		$this -> cache = $path;
		Local::createFolder($this -> cache);
	}
	
	public function getRealCache() {
		return $this -> cache;
	}
	
	// группа работы с роутером
	
	public function route() {
		return $this -> router -> route;
	}
	
	public function template() {
		return $this -> router -> template['name'];
	}
	
	public function section() {
		return $this -> router -> template['section'];
	}
	
	public function page() {
		$entry = System::typeClass($this -> router -> current, 'entry');
		return $entry ? $this -> router -> current -> getEntryData('name') : null;
	}
	
	public function parents() {
		$entry = System::typeClass($this -> router -> current, 'entry');
		return $entry ? $this -> router -> current -> getEntryKey('parents') : null;
	}
	
	public function type() {
		$entry = System::typeClass($this -> router -> current, 'entry');
		return $entry ? $this -> router -> current -> getEntryKey('type') : null;
	}
	
	public function pageByLang() {
		$name = $this -> page();
		return $name ? $this -> lang('menu:' . $name) : null;
	}
	
	public function parentsByLang() {
		$parents = $this -> parents();
		$result = [];
		if (System::typeIterable($parents)) {
			foreach ($parents as $item) {
				$name = $this -> lang('menu:' . $item);
				$result[] = $name ? $name : $item;
			}
			unset($key, $item);
		}
		return $result;
	}
	
	public function routeByLang() {
		$route = $this -> route();
		$result = [];
		if (System::typeIterable($route)) {
			foreach ($route as $item) {
				$name = $this -> lang('menu:' . $item);
				$result[] = $name ? $name : $item;
			}
			unset($key, $item);
		}
		return $result;
	}
	
	// группа работы с определением
	
	public function main() {
		// адрес главной страницы шаблона/раздела
		$url = $this -> uri();
		$route = Strings::join($this -> route(), '/');
		$pos = $route ? Strings::find($url, $route) : null;
		return Strings::get($url, 0, $pos);
	}
	
	public function home() {
		// адрес домашней страницы
		return $this -> domain();
	}
	
	public function matchPage($name) {
		// проверка на название страницы
		return $this -> page() === $name ? true : null;
	}
	
	public function matchMain() {
		// проверка на главную страницу шаблона/раздела
		return $this -> route() ? null : true;
	}
	
	public function matchHome() {
		// проверка на домашнюю страницу
		return $this -> uriPathArray() ? null : true;
	}
	
}

?>