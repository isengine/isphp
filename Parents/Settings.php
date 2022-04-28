<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

class Settings extends Data
{
    public $defaults;

    public function __construct($settings, $index = null)
    {
        if ($settings) {
            if ($index) {
                $this->setDefaultByIndex($settings);
            } else {
                $this->setDefault($settings);
            }
        }
    }

    public function setSettings($settings)
    {
        $this->mergeData($settings, true);
    }

    public function resetSettings()
    {
        $this->setData(
            $this->defaults
        );
    }

    public function setDefault($settings)
    {
        $this->defaults = Objects::create($settings);
        $this->resetSettings();
    }

    public function setDefaultByIndex($settings)
    {
        $this->defaults = Objects::createByIndex($settings);
        $this->resetSettings();
    }
}
