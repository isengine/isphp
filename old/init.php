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
System::includes('helpers:prepare');
System::includes('helpers:strings');
System::includes('helpers:objects');
System::includes('helpers:match');
System::includes('helpers:parser');
System::includes('helpers:sessions');
System::includes('helpers:ip');
System::includes('helpers:local');
System::includes('helpers:paths');

// parents
System::includes('model:parents:data');
System::includes('model:parents:entry');
System::includes('model:parents:collection');
System::includes('model:parents:singleton');
System::includes('model:parents:constants');
System::includes('model:parents:globals');

// interfaces
// traits

// data
//System::includes('model:data:localdata');

// components
System::includes('model:components:config');
System::includes('model:components:state');
System::includes('model:components:log');
System::includes('model:components:error');
System::includes('model:components:display');
System::includes('model:components:session');
System::includes('model:components:uri');
System::includes('model:components:user');
System::includes('model:components:collection');
System::includes('model:components:filter');
System::includes('model:components:language');
System::includes('model:components:router');

// database
System::includes('model:databases:database');
System::includes('model:databases:datasheet');
System::includes('model:databases:driver');
System::includes('model:databases:drivers:localdb');
System::includes('model:databases:drivers:tabledb');
System::includes('model:databases:drivers:exceldb');

// api
System::includes('model:apis:api');
System::includes('model:apis:method');
//System::includes('model:apis:methods:files');

// templates
System::includes('model:templates:template');
System::includes('model:templates:view');
System::includes('model:templates:views:defaultview');

?>