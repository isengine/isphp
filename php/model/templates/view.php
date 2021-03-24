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

abstract class View extends Data {
	
	/*
	это фактически интерфейс метода
	работаем с подготовленными запросами
	*/
	
	public function __construct($data = null) {
		if ($data) {
			$this -> setData($data);
		}
	}
	
}

?>