<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Matches;

class Map extends Data
{
    public $count;
    public $parents;
    public $total;

    public function count($list, $tags = null)
    {
        // tags предполагает, что родительские группы
        // являются не вложениями, а тегами
        // соответственно, подсчет идет по-разному

        // пример
        //   a:item
        //   b:item
        //   a:b:item
        //  tags = null (по-умолчанию), результат:
        //   a = 1
        //   b = 1
        //   a:b = 1
        //   total = 3
        //  tags = true, результат:
        //   a = 2
        //   b = 2
        //   total = 3

        $this->count = [];
        $this->total = null;

        foreach ($list as $item) {
            $o = $item;
            $item = $this->convert($item);
            $item = Objects::unlast($item);

            if (!$item) {
                $this->total++;
                continue;
            }

            $k = null;
            foreach ($item as $i) {
                if (!$tags) {
                    if ($k) {
                        $k .= ':';
                    } else {
                        $this->total++;
                    }
                    $k .= $i;
                }
                $val = $tags ? $i : $k;
                $this->count[$val] = empty($this->count[$val]) ? 1 : $this->count[$val] + 1;
            }
            if ($tags) {
                $this->total++;
            }
            unset($i);
        }
        unset($item);

        return $this->count;
    }

    public function total()
    {
        return $this->total;
    }

    public function build($list, $value = null)
    {
        $this->reset();
        foreach ($list as $item) {
            $this->addMap($item, $value);
            $this->addParents($item, $value);
        }
        unset($item);
    }

    public function getMap($name = null)
    {
        return $name ? Objects::extract($this->data, $this->convert($name)) : $this->getData();
    }

    public function addMap($name, $value = null)
    {
        $this->data = Objects::inject($this->data, $this->convert($name), $value);
    }

    public function removeMap($name)
    {
        $this->data = Objects::delete($this->data, $this->convert($name));
    }

    public function addParents($name, $value = null)
    {
        //$this->parents = Objects::inject($this->parents, $this->convert( Strings::before($name, ':', null, true) ), $value);
        //$this->parents = Objects::inject($this->parents, $this->convert($name), $value);
        $this->parents = Objects::inject(
            $this->parents ? $this->parents : [],
            $this->convert(Strings::before($name, ':', null, true)),
            $value
        );
    }

    public function removeParents($name)
    {
        $this->parents = Objects::delete($this->parents, $this->convert($name));
    }

    public function convert($name)
    {
        return Strings::split($name, ':');
    }

    public function reset()
    {
        $this->data = [];
    }
}
