<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Matches;
use is\Helpers\Sessions;
use is\Helpers\Prepare;
use is\Helpers\Parser;
use is\Parents\Globals;

class Language extends Globals
{
    public $lang;

    public $settings = [];
    public $list = [];
    public $codes = [];
    public $code;

    public function init()
    {
        $list = [
            'langs' => null,
            'arr' => strtolower(System::server('HTTP_ACCEPT_LANGUAGE', true))
        ];

        if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list['arr'], $list['arr'])) {
            $list['langs'] = array_combine(
                (array) $list['arr'][1],
                (array) $list['arr'][2]
            );
            foreach ($list['langs'] as $key => $item) {
                $list['langs'][$key] = $item ? $item : 1;
            }
            unset($key, $item);
            arsort($list['langs'], SORT_NUMERIC);
        } else {
            $list['langs'] = [];
        }

        $lang = Objects::first($list['langs'], 'key');

        unset($list);

        $lang = Strings::split($lang, '-');
        $lang = Objects::first($lang, 'value');
        $lang = Prepare::lower($lang);

        $this->lang = $lang;
    }

    public function setLang($lang)
    {
        if (!$lang) {
            $this->init();
            return;
        }

        $lang = Strings::split($lang, '-');
        $lang = Objects::first($lang, 'value');
        $lang = Prepare::lower($lang);

        $lang = $this->mergeLang($lang);

        if (!$lang) {
            $lang = Objects::first($this->list, 'value');
        }

        $this->lang = $lang;
        $this->setCode();
    }

    public function mergeLang($lang)
    {
        return $lang && isset($this->list[$lang]) ? $this->list[$lang] : null;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function addList($key, $array = null)
    {
        if (
            System::set($array)
            && System::typeOf($array, 'iterable')
        ) {
            $this->list = Objects::merge($this->list, Objects::join($array, $key));
        } else {
            $this->list[$key] = $key;
        }
    }

    public function addCode($key, $item = null)
    {
        $item = Prepare::upper($item ? $item : $key);

        $this->codes[$key] = $item;
    }

    public function setCode()
    {
        $this->code = $this->codes[$this->lang];
    }
}
