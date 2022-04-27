<?php

namespace is\Components;

use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Parents\Globals;

class Error extends Globals
{
    public $path;

    public $code;
    public $reason;

    public $prefix;
    public $postfix;

    public function init($path = null)
    {
        $this->setPath($path);
        $this->data['Content-Type'] = 'text/html; charset=UTF-8';
    }

    public function setPath($path = null)
    {
        $this->path = Paths::prepareUrl($path);
    }

    public function setError($code = null)
    {
        //$this->data['Error-Сode'] = $this->code;
        //$this->data['Error-Reason'] = $this->reason;

        if (!$code) {
            $this->code = $code;
        }

        if (headers_sent()) {
            return;
        }

        Sessions::setHeader($this->data);
        Sessions::setHeaderCode($this->code);
    }

    public function reload()
    {
        //$this->data['Error-Сode'] = $this->code;
        //$this->data['Error-Reason'] = $this->reason;

        $path = $this->path . $this->prefix . $this->code . $this->postfix . ($this->reason ? $this->reason : null);

        Sessions::reload($path, $this->code, $this->data);
        exit;
    }
}
