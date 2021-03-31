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
use is\Helpers\Paths;
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
	
	public function langListCode() {
		return $this -> lang -> codes;
	}
	
	public function lang($data) {
		if (Strings::match($data, ':')) {
			$data = Parser::fromString($data);
			$array = $this -> lang -> getData();
			return Objects::extract($array, $data);
		} else {
			return $this -> lang -> getData($data);
		}
	}
	
	// группа работы с uri
	
	public function url() {
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
	
	// группа работы с парсингом
	
	public function parse($string) {
		// парсер текстовых переменных
		return Parser::textVariables($string, function($type, $data){
			
			// для ссылок и тегов здесь общее правило такое:
			// пути и ключевые значения
			// затем классы
			// затем альты
			
			$result = null;
			
			if ($type === 'lang') {
				
				$result = $this -> lang( Strings::join($data, ':') );
				
			} elseif ($type === 'url') {
				
				$url = $data[0];
				$absolute = Strings::find($url, '//') === 0 ? ' target="_blank"' : null;
				$class = $data[1] ? ' class="' . $data[1] . '"' : null;
				
				$result = '<a href="' . $url . '" alt="' . $data[2] . '"' . $class . $absolute . '>' . $data[2] . '</a>';
				
				//echo print_r(htmlentities($result), 1) . '<br>';
				
			} elseif ($type === 'mail') {
				
				$url = $data[0];
				$class = $data[1] ? ' class="' . $data[1] . '"' : null;
				
				if (!$data[2]) {
					$data[2] = $url;
				}
				
				$subject = $data[3] ? '?subject=' . $data[3] : null;
				
				$result = '<a href="mailto:' . $url . $subject . '" alt="' . $data[2] . '"' . $class . '>' . $data[2] . '</a>';
				
				//echo print_r(htmlentities($result), 1) . '<br>';
				
			} elseif ($type === 'phone') {
				
				$url = $data[0];
				$class = $data[1] ? ' class="' . $data[1] . '"' : null;
				
				if (!$data[2]) {
					$data[2] = $url;
				}
				
				$url = Prepare::phone($url, $this -> langName());
				
				$result = '<a href="tel:' . $url . '" alt="' . $data[2] . '"' . $class . '>' . $data[2] . '</a>';
				
			} elseif ($type === 'url') {
				
				$url = $data[0];
				$absolute = Strings::find($url, '//') === 0 ? ' target="_blank"' : null;
				$class = $data[1] ? ' class="' . $data[1] . '"' : null;
				
				$result = '<a href="' . $url . '" alt="' . $data[2] . '"' . $class . $absolute . '>' . $data[2] . '</a>';
				
				//echo print_r(htmlentities($result), 1) . '<br>';
				
			} elseif ($type === 'icon') {
				
				$result = '<i class="' . $data[0] . '" aria-hidden="true"></i>';
				
			} elseif ($type === 'img') {
				
				// с помощью srcset можно организовать правильный lazyload
				// для этого нужно установить js библиотеку
				// и указать изображению соответствующий класс
				
				// https://apoorv.pro/lozad.js/
				// <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/lozad/dist/lozad.min.js"></script>
				// lozad('.demilazyload').observe();
				// lozad( document.querySelector('img') ).observe();
				
				$url = $data[0];
				if (Strings::find($url, '//') !== 0) {
					$url = Paths::prepareUrl($url);
				}
				
				$srcset = $data[1];
				if ($srcset) {
					$srcset = ' srcset="' . $srcset . '" data-srcset="' . $url . '"';
				}
				
				$class = $data[2] ? ' class="' . $data[2] . '"' : null;
				
				$result = '<img src="' . $url . '"' . $srcset . ' alt="' . $data[3] . '"' . $class . ' />';
				
				//echo print_r(htmlentities($result), 1) . '<br>';
				
			}
			
			return $result;
			
		});
	}
	
}

?>