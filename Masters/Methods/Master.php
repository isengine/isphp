<?php

namespace is\Masters\Methods;

use is\Helpers\Local;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\System;
use is\Helpers\Prepare;
use is\Helpers\Matches;
use is\Parents\Data;

abstract class Master extends Data
{
    /*
    это фактически интерфейс метода
    работаем с подготовленными запросами
    */

    public function __construct($data = null)
    {
        if ($data) {
            $this->setData($data);
        }
    }

    abstract public function launch();
}
