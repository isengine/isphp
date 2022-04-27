<?php

namespace is\Masters\Drivers\Master;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

class Errors extends Verify
{
    public $errors; // массив ошибок

    public function setError($name, $status = null)
    {
        // задать ошибку
        $this->errors[] = $name;
    }

    public function getError($name = null)
    {
        // получить ошибки
        if (!System::set($name)) {
            return $this->errors;
        }
        return $this->errors[$name];
    }

    public function resetErrors()
    {
        // стереть все ошибки
        $this->errors = [];
    }

    public function success()
    {
        // проверить статус обращения к бд
        // успешно или есть ошибки
        return !Objects::len($this->errors);
    }
}
