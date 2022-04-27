<?php

namespace is\Masters\Channels;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Parents\Data;

abstract class Master extends Data
{
    public $error;
    public $status;

    public function __construct($data = null)
    {
        if (System::typeIterable($data)) {
            $this->setData($data);
        }
    }

    // интерфейс для реализации в классе

    abstract public function receive();
    abstract public function send();
    abstract public function connect();

    // базовые функции получения свойств

    public function status()
    {
        return $status;
    }

    public function error()
    {
        return $error;
    }

    public function success()
    {
        return !$error;
    }

    // базовые функции задания свойств

    public function set($first, $second = null)
    {
        if (is_array($first)) {
            foreach ($first as $key => $item) {
                $this->set($key, $item);
            }
            unset($key, $item);
        } elseif (System::set($first)) {
            $this->$first = $second;
        }
    }

    // внутренние функции задания свойств

    public function setStatus($data)
    {
        $this->status = $data;
    }

    public function setError($data)
    {
        $this->error = $data;
    }
}
