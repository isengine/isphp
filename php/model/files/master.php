<?php

namespace is\Model\Files;

use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;
use is\Helpers\System;
use is\Helpers\Match;
use is\Helpers\Paths;

use is\Model\Components\Display;
use is\Model\Views\View;

abstract class Master extends Display {
	
	abstract public function launch();
	
}

?>