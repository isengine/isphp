<?php

namespace is\Model\Databases;

use is\Helpers\Sessions;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\Prepare;
use is\Model\Parents\Singleton;
use is\Model\Parents\Collection;
use is\Model\Parents\Data;
use is\Model\Databases\Driver;

class Datasheet extends Database {
	
	public function __construct() { }
	public function __clone() { }
	public function __wakeup() { }
	public static function getInstance() { }
	
}

?>