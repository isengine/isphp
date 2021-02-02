<?php

namespace is;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;

require_once __DIR__ . DS . 'helpers' . DS . 'system.php';

// helpers
System::include('helpers:prepare');
System::include('helpers:strings');
System::include('helpers:objects');
System::include('helpers:match');
System::include('helpers:parser');
System::include('helpers:sessions');

// interfaces
//System::include('model:interfaces:iglobals');
//System::include('model:interfaces:uri');

// traits
//System::include('model:traits:globals');

// parents
System::include('parents:data');
System::include('parents:entry');
System::include('parents:collection');
System::include('parents:catalog');
System::include('parents:singleton');
System::include('parents:constants');
System::include('parents:globals');
System::include('parents:path');
System::include('parents:local');
//System::include('parents:include'); класс, реализующий подключение кода php

// constants
System::include('model:constants:config');

// globals
System::include('model:globals:session');
System::include('model:globals:uri');

// data
System::include('model:data:localdata');

?>