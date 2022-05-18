<?php

namespace is\Masters;

use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;
use is\Helpers\System;
use is\Helpers\Matches;
use is\Helpers\Paths;
use is\Helpers\Prepare;
use is\Parents\Singleton;

class View extends Singleton
{
    // общие установки кэша

    public function add($name)
    {
        $n = Prepare::upperFirst($name);
        $ns = __NAMESPACE__ . '\\Extenders\\' . $n . '\\' . $n;
        $this->data[$name] = new $ns();
    }

    public function set($name, $object)
    {
        $this->data[$name] = $object;
    }

    public function get($type = null, $parameters = null)
    {
        if (!System::set($type)) {
            return null;
        }
        if (Strings::match($type, '|')) {
            $array = Strings::pairs($type, '|');
            return $this->data[$array[0]]->get($array[1], $parameters);
        }
        return !empty($this->data[$type]) ? $this->data[$type] : null;
    }

    public function call($data, $params = null)
    {
        $data = Parser::fromString($data);

        if (!$data[0] || !$data[1] || !System::typeIterable($data)) {
            return null;
        }

        $type = $data[0];
        $func = $data[1];

        return $this->data[$type]->$func($params);
    }

    public function reset($type)
    {
        $this->deleteDataKey($type);
    }
}

// now use
// echo $view->get('lang')->get('information:work:0');
// echo $view->get('lang|information:work:0');
// echo $view->call('lang:get', 'information:work:0');
