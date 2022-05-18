<?php

namespace is\Masters;

use is\Helpers\Sessions;
use is\Helpers\Prepare;
use is\Parents\Singleton;

class Api extends Singleton
{
    public $class;
    public $method;

    public $key;
    public $token;

    public $settings;

    public function init($settings)
    {
        $this->class = $settings['class'];
        $this->method = $settings['method'];

        if ($settings['key']) {
            $this->setKey($settings['key']);
        }

        if ($settings['token']) {
            $this->setToken($settings['token']);
        }

        if ($settings['data']) {
            $this->setData($settings['data']);
        }
    }

    public function error()
    {
        Sessions::setHeaderCode(404);
        exit;
    }

    public function launch()
    {
        if (!$this->class || !$this->method) {
            $this->error();
        }

        $class_name = __NAMESPACE__ . '\\Methods\\'
            . Prepare::upperFirst($this->class) . '\\'
            . Prepare::upperFirst($this->method);

        if (!class_exists($class_name)) {
            $this->error();
        }

        $class = new $class_name($this->getData());

        //$method = $this->method;
        //if (!method_exists($class, $method)) {
        //    $this->error();
        //}
        //$class->$method();

        $class->launch();
    }

    public function setKey($key)
    {
        $this->key = json_decode(Prepare::decode($key), true);
    }

    public function setToken($token)
    {
        $this->token = [
            'current' => time(),
            'request' => Prepare::decode($token)
        ];
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }
}
