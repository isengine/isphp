<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Matches;
use is\Helpers\Sessions;
use is\Helpers\Ip;
use is\Helpers\Prepare;
use is\Parents\Globals;

class Session extends Globals
{
    protected $init;

    protected $agent;
    protected $referrer;
    protected $origin;
    protected $request;

    protected $id;
    protected $token;
    protected $time;
    protected $ip;

    protected $cookie;

    public function init()
    {
        $time = new \DateTime();

        $this->init = $time->format('Y.m.d-H.i.s.u');

        $this->agent = Prepare::hash(System::server('agent'));
        $this->referrer = System::server('referrer');
        $this->request = Prepare::lower(System::server('method'));

        $origin = System::server('ORIGIN', true);
        $origin_http = System::server('HTTP_ORIGIN', true);
        $this->origin = $origin && !empty($origin) ? $origin : $origin_http;

        $this->id = session_id();
        $this->ip = Ip::real();

        if ($this->id) {
            $t = $this->getValue('token');
            if ($t) {
                $this->token = $t;
            } else {
                $token = Prepare::encode(json_encode([
                    'id' => $this->id,
                    'ip' => $this->ip,
                    'agent' => $this->agent,
                    'time' => time()
                ]));

                $this->token = $token;
                $this->setValue('token', $token);

                unset($token);
            }
        }
    }

    public function reinit()
    {
        session_regenerate_id(true);
        $this->setValue('token', null);
        $this->init();
        Sessions::setCookie('session', $this->token);
        //Sessions::setCookie('session', $_SESSION['token']);
        //Sessions::setCookie('session', $session->getSession('token'));
    }

    public function open()
    {
        session_start();
    }

    public function close()
    {
        if (session_id()) {
            session_unset();
            session_destroy();
        } else {
            $_SESSION = [];
        }

        $cookies = Objects::keys(Sessions::getCookie());
        Sessions::unCookie($cookies);
    }

    public function setCsrf()
    {
        $this->setValue('csrf-match', $this->getValue('csrf-token'));
        $token = Prepare::hash(time());
        $this->setValue('csrf-token', $token);

        if (!$this->getValue('csrf-match')) {
            $this->setValue('csrf-match', $token);
        }
        Sessions::setHeader(['X-CSRF-Token' => $token]);
    }

    public function getCsrf()
    {
        return $this->getValue('csrf-token');
    }

    public function matchCsrf($match)
    {
        return $match && $this->getValue('csrf-match') === $match;
    }

    public function getSession($name)
    {
        return $this->$name;
    }

    public function getValue($name)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    public function setValue($name, $data)
    {
        $_SESSION[$name] = $data;
    }
}
