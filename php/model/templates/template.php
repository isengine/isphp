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
	
	/*
	НУЖНО НАСТРОИТЬ ВИД ТАК, ЧТОБЫ ОН СЧИТЫВАЛ НУЖНЫЙ PHP-ФАЙЛ
	ЗАГРУЖАЯ ЕГО ЧЕРЕЗ OB_START
	ЗАТЕМ ПЕРЕБРАСЫВАЯ ДАННЫЕ В ПЕРЕМЕННУЮ
	ЗАТЕМ РАСПАРСИВАЯ ТЕКСТОВЫЕ ПЕРЕМЕННЫЕ
	И ТОЛЬКО ЗАТЕМ ОТПРАВЛЯТЬ ЕГО НА ВЫВОД
	
	КЭШИРОВАНИЕ И ЗАГРУЗКА СТРАНИЦ ДОЛЖЫ БЫТЬ ОТДЕЛЬНЫМ КЛАССОВ
	СВЯЗЬ ПОД ВИДОМ БУДЕТ ЧЕРЕЗ ИНИЦИАЛИЗАЦИЮ ЛИБО
	- РОДИТЕЛЬСКОГО КЛАССА ИЛИ КЛАССА, ВЫЗВАВШЕГО ПОДКЛАСС, (TEMPLATE от VIEW) ВОЗМОЖНО СВЯЗЬ БУДЕТ ИДТИ ЧЕРЕЗ ПЕРЕМЕННУЮ STATIC::CLASS
	- ВЫЗОВА TEMPLATE -> ...
	*/
	
	public function init($settings = []) {
		$this -> settings = $settings;
		$this -> seo = new Data;
	}
	
	public function launch() {
		$viewname = __NAMESPACE__ . '\\Views\\' . ($this -> settings['view'] ? $this -> settings['view'] : 'DefaultView');
		$this -> view = new $viewname($this -> settings['path']);
		$this -> view -> setRealCache($this -> settings['cache']);
	}
	
	public function lang() {
		return Language::getInstance();
	}
	
	public function router() {
		return Router::getInstance();
	}
	
	public function uri() {
		return Uri::getInstance();
	}
	
	public function parse($string) {
		// парсер текстовых переменных
		return Parser::textVariables($string, function($type, $data){
			
			// для ссылок и тегов здесь общее правило такое:
			// пути и ключевые значения
			// затем классы
			// затем альты
			
			$varname = __NAMESPACE__ . '\\Variables\\' . (Prepare::upperFirst($type));
			$var = new $varname($data);
			
			return $var -> init();
			
		});
	}
	
}

?>