<?php

namespace is;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Prepare;

use is\Parents\Path;
use is\Parents\Collection;
use is\Parents\Catalog;

use is\Model\Globals\Uri;
use is\Model\Globals\Session;
use is\Model\Constants\Config;

$real = __DIR__ . DS;
$real = '..';
$url = null;

$host = new Path($real);
$host -> data = ['tratata', 'blablabla'];
//$host -> real = realpath(__DIR__);
//$host -> initialize();

echo '<pre>REAL: ' . print_r($url, 1) . '</pre>';
echo '<pre>URL: ' . print_r($real, 1) . '</pre>';
echo '<pre>HOST: ' . print_r($host, 1) . '</pre>';
?>