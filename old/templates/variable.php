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

class Variable extends Data {
	
	// по $data для ссылок и тегов здесь общее правило такое:
	// пути и ключевые значения
	// затем классы
	// затем альты
	
	public function __construct() {
	}
	
	public function init($string) {
		return Parser::textVariables($string, function($type, $data){
			$name = __NAMESPACE__ . '\\Variables\\' . (Prepare::upperFirst($type));
			$var = new $name($data);
			return $var -> init();
		});
	}
	
}

?>