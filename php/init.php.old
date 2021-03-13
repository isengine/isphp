<?php

// Рабочее пространство имен

namespace is;

use is\Helpers\System;

// Базовые константы

if (!defined('isENGINE')) { define('isENGINE', microtime(true)); }
if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }
if (!defined('DP')) { define('DP', '..' . DIRECTORY_SEPARATOR); }
if (!defined('DR')) { define('DR', realpath(__DIR__ . DS . DP . DP . DP) . DS); }

// Подключение классов

require_once __DIR__ . DS . 'helpers' . DS . 'system.php';

// helpers
System::include('helpers:prepare');
System::include('helpers:strings');
System::include('helpers:objects');
System::include('helpers:match');
System::include('helpers:parser');
System::include('helpers:sessions');
System::include('helpers:ip');
System::include('helpers:local');
System::include('helpers:url');

// interfaces

// traits

// parents
System::include('parents:data');
System::include('parents:entry');
System::include('parents:collection');
System::include('parents:singleton');
System::include('parents:constants');
System::include('parents:globals');

// constants

// globals
System::include('model:globals:session');
System::include('model:globals:uri');

// data
//System::include('model:data:localdata');

// components
System::include('model:components:config');
System::include('model:components:state');
System::include('model:components:catalog');
System::include('model:components:local');
System::include('model:components:content');
System::include('model:components:log');
System::include('model:components:path');
System::include('model:components:api');
System::include('model:components:error');
System::include('model:components:display');
System::include('model:components:session');
System::include('model:components:uri');

// controller
System::include('model:controller:database');
System::include('model:controller:driver');

// drivers
System::include('model:controller:drivers:local');

?>