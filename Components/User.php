<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Matches;
use is\Helpers\Sessions;
use is\Parents\Entry;
use is\Parents\Globals;

class User extends Globals
{
    public $settings;
    public $special;

    public function init()
    {
        unset($this->data);
        $this->data = new Entry();
    }

    public function isset()
    {
        return System::set($this->data);
    }

    public function setSettings($settings)
    {
        $this->settings = json_decode(json_encode($settings), true);
    }

    public function setSpecial()
    {
        $this->special = [];
        if ($this->settings) {
            foreach ($this->settings as $key => $item) {
                if (!empty($item['special'])) {
                    $this->special[ $item['special'] ][] = $key;
                }
            }
            unset($key, $item);
        }
    }

    public function getFields($name = null)
    {
        return $name ? $this->data['data'][$name] : $this->data['data'];
    }

    public function getFieldsBySpecial($name, $all = null)
    {
        $result = [];
        $specials = isset($this->special[$name]) ? $this->special[$name] : null;

        if (!$specials) {
            return null;
        }

        foreach ($specials as $item) {
            $r = $this->data->getData($item);
            if ($all) {
                $result[] = $r;
            } else {
                $result = $r;
                break;
            }
        }
        unset($item);

        return $result;
    }

    public function setFieldsBySpecial($name, $data)
    {
        $field = $this->getFieldsNameBySpecial($name);
        $this->data->setData($field, $data);
    }

    public function addFieldsBySpecial($name, $data)
    {
        $field = $this->getFieldsNameBySpecial($name);
        $this->data->addData($field, $data);
    }

    public function getFieldsNameBySpecial($name, $all = null)
    {
        $specials = $this->special[$name];

        return $all ? $specials : Objects::first($specials, 'value');
    }
}
