<?php

namespace is\Parents;

use is\Helpers\System;

class Entry extends Data
{
    public $id;
    public $name;
    public $type;
    public $parents;
    public $ctime;
    public $mtime;
    public $dtime;
    public $owner;

    public function getEntry()
    {
        $result = [];

        $array = [
            'id', 'name', 'type', 'parents', 'ctime', 'mtime', 'dtime', 'owner'
        ];

        foreach ($array as $item) {
            $result[$item] = $this->$item;
        }
        unset($item);

        $result['data'] = $this->data;

        return $result;
    }

    public function getEntryKey($key)
    {
        if (System::set($key)) {
            return $this->$key;
        }
    }

    public function getEntryData($key = null)
    {
        return $this->getData($key);
    }

    public function setEntry($data)
    {
        $array = [
            'id', 'name', 'type', 'parents', 'ctime', 'mtime', 'dtime', 'owner'
        ];

        $data = (array) $data;

        foreach ($array as $item) {
            $this->setEntryKey($item, isset($data[$item]) ? $data[$item] : null);
        }
        unset($item);

        $this->setData($data['data']);
    }

    public function setEntryKey($key, $data)
    {
        if (System::set($key)) {
            $this->$key = $data;
        }
    }
}
