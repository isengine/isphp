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
System::include('helpers:paths');

// parents
System::include('model:parents:data');
System::include('model:parents:entry');
System::include('model:parents:collection');
System::include('model:parents:singleton');
System::include('model:parents:constants');
System::include('model:parents:globals');

// interfaces
// traits

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
System::include('model:components:error');
System::include('model:components:display');
System::include('model:components:session');
System::include('model:components:uri');
System::include('model:components:user');
System::include('model:components:collection');
System::include('model:components:filter');
System::include('model:components:language');
System::include('model:components:router');

// database
System::include('model:databases:database');
System::include('model:databases:datasheet');
System::include('model:databases:driver');
System::include('model:databases:drivers:localdb');
System::include('model:databases:drivers:tabledb');
System::include('model:databases:drivers:exceldb');

// api
System::include('model:apis:api');
System::include('model:apis:method');
//System::include('model:apis:methods:files');

// templates
System::include('model:templates:template');
System::include('model:templates:view');
System::include('model:templates:views:defaultview');

?>