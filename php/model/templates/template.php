<?php

namespace is\Model\Templates;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\Prepare;
use is\Model\Parents\Data;
use is\Model\Parents\Singleton;
use is\Model\Components\Language;
use is\Model\Components\Router;
use is\Model\Components\Uri;

class Template extends Singleton {
	
	public $settings;
	public $view;
	public $seo;
	public $render;
	
	/*
	НУЖНО НАСТРОИТЬ ВИД ТАК, ЧТОБЫ ОН СЧИТЫВАЛ НУЖНЫЙ PHP-ФАЙЛ
	ЗАГРУЖАЯ ЕГО ЧЕРЕЗ OB_START
	ЗАТЕМ ПЕРЕБРАСЫВАЯ ДАННЫЕ В ПЕРЕМЕННУЮ
	ЗАТЕМ РАСПАРСИВАЯ ТЕКСТОВЫЕ ПЕРЕМЕННЫЕ
	И ТОЛЬКО ЗАТЕМ ОТПРАВЛЯТЬ ЕГО НА ВЫВОД
	*/
	
	public function init($settings = []) {
		
		// инициализация настроек
		$this -> settings = new Data;
		
		// инициализация seo
		$this -> seo = new Data;
		
		// инициализация видов
		$this -> view = new View($settings['path'], $settings['cache']);
		
		// настройки рендеринга
		$this -> render = $settings['render'];
		
		unset($settings);
		
	}
	
	// группа работы с определением
	
	public function main() {
		// адрес главной страницы шаблона/раздела
		$url = $this -> get('url');
		$route = Strings::join($this -> get('route'), '/');
		$pos = $route ? Strings::find($url, $route) : null;
		return Strings::get($url, 0, $pos);
	}
	
	public function home() {
		// адрес домашней страницы
		return $this -> get('domain');
	}
	
	public function match($type, $name = null) {
		if ($type === 'page') {
			// проверка на название страницы
			return $this -> get('page') === $name;
		} elseif ($type === 'main') {
			// проверка на главную страницу шаблона/раздела
			return $this -> get('route') ? null : true;
		} elseif ($type === 'home') {
			// проверка на домашнюю страницу
			return $this -> get('home');
		}
	}
	
	// группа краткого вызова компонентов
	
	public function lang() {
		return Language::getInstance();
	}
	
	public function parse() {
		return new Variable;
	}
	
	public function render($type, $name) {
		// вызов рендеринга
		// например, render('css', 'filename')
		$name = __NAMESPACE__ . '\\Renders\\' . (Prepare::upperFirst($type));
		$render = new $name(
			$this -> render['from'],
			$this -> render['to'],
			$this -> render['url']
		);
		$render -> launch($name);
	}
	
}

?>