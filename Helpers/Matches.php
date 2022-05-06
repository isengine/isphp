<?php

namespace is\Helpers;

class Matches
{
    public static function equal($haystack, $needle)
    {
        // функция задействует сравнение, приводя данные к строке

        return (string) $haystack === (string) $needle ? true : null;
    }

    public static function string($haystack, $needle)
    {
        // функция проверяет наличие строки

        return Strings::match($haystack, $needle);
    }

    public static function regexp($haystack, $regexp)
    {
        // функция проверяет наличие строки по регулярному выражению

        return preg_match('/' . $regexp . '/u', $haystack);
    }

    public static function mask($haystack, $mask)
    {
        // функция проверяет наличие строки по маске

        return self::regexp($haystack, self::maskToRegexp($mask));
    }

    public static function numeric($haystack, $min = null, $max = null)
    {
        // функция сравнивает число в диапазоне от мин до макс включительно
        // если мин/макс не заданы, то считаются минус/плюс бесконечностью

        $haystack = Prepare::numeric($haystack);
        $min = System::set($min) ? (float) $min : false;
        $max = System::set($max) ? (float) $max : false;

        $rmin = $min === false ? true : $haystack >= $min;
        $rmax = $max === false ? true : $haystack <= $max;

        return $rmin && $rmax ? true : null;
    }

    public static function equalIn($haystack, $needle, $and = null)
    {
        // функция задействует сравнение, приводя данные к строке
        // сравнение идет с массивом haystack

        $result = null;

        foreach ($haystack as $item) {
            $result = self::equal($item, $needle);
            if (
                ($and && !$result) ||
                (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);

        return $result;
    }

    public static function stringIn($haystack, $needle, $and = null)
    {
        // функция проверяет наличие строки
        // сравнение идет с массивом haystack

        $result = null;

        foreach ($haystack as $item) {
            $result = self::string($item, $needle);
            if (
                ($and && !$result) ||
                (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);

        return $result;
    }

    public static function regexpIn($haystack, $regexp, $and = null)
    {
        // функция проверяет наличие строки по регулярному выражению
        // сравнение идет с массивом haystack

        foreach ($haystack as $item) {
            $result = self::regexp($item, $regexp);
            if (
                ($and && !$result) ||
                (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);

        return $result;
    }

    public static function maskIn($haystack, $mask, $and = null)
    {
        // функция проверяет наличие строки по маске
        // сравнение идет с массивом haystack

        return self::regexpIn($haystack, self::maskToRegexp($mask));
    }

    public static function numericIn($haystack, $min = null, $max = null, $and = null)
    {
        // функция сравнивает число в диапазоне от мин до макс включительно
        // если мин/макс не заданы, то считаются минус/плюс бесконечностью
        // сравнение идет с массивом haystack

        $result = null;

        foreach ($haystack as $item) {
            $result = self::numeric($item, $min, $max);
            if (
                ($and && !$result) ||
                (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);

        return $result;
    }

    public static function equalOf($haystack, $needle, $and = null)
    {
        // функция задействует сравнение, приводя данные к строке
        // сравнение идет по массиву needle

        return self::equalIn($needle, $haystack, $and);
    }

    public static function stringOf($haystack, $needle, $and = null)
    {
        // функция проверяет наличие строки
        // сравнение идет по массиву needle

        return self::stringIn($needle, $haystack, $and);
    }

    public static function regexpOf($haystack, $regexp, $and = null)
    {
        // функция проверяет наличие строки по регулярному выражению
        // сравнение идет по массиву регулярных выражений regexp

        foreach ($regexp as $item) {
            $result = self::regexp($haystack, $item);
            if (
                ($and && !$result) ||
                (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);

        return $result;
    }

    public static function maskOf($haystack, $mask, $and = null)
    {
        // функция проверяет наличие строки по маске
        // сравнение идет по массиву масок mask

        foreach ($mask as $item) {
            $result = self::mask($haystack, $item);
            if (
                ($and && !$result) ||
                (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);

        return $result;
    }

    public static function numericOf($haystack, $minmax, $and = null)
    {
        // функция сравнивает число в диапазоне от мин до макс включительно
        // если мин/макс не заданы, то считаются минус/плюс бесконечностью
        // сравнение идет по массиву значений minmax

        $result = null;

        foreach ($minmax as $item) {
            $result = self::numeric($haystack, Objects::first($item, 'value'), Objects::last($item, 'value'));
            if (
                ($and && !$result) ||
                (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);

        return $result;
    }

    public static function maskToRegexp($mask)
    {
        // функция преобразования маски в регулярное выражение

        return $mask = '^' . str_replace(['\*', '\?'], ['.*', '.'], preg_quote($mask)) . '$';
    }
}
