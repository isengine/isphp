<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Parents;

class Globals extends Parents\Globals
{
    public function init()
    {
    }

    public function set($key, $value)
    {
        if (!$key) {
            return;
        }

        $map = $this->convert($key);
        $this->data = Objects::inject($this->data, $map, $value);
    }

    public function get($key = null, $from = null)
    {
        if (!$key) {
            return $this->getData();
        }

        $map = $this->convert($key);
        return Objects::extract($this->data, $map);
    }

    protected function convert($key)
    {
        return $key ? Strings::split(mb_strtoupper($key), ':') : [mb_strtoupper($key)];
    }
}
