<?php

namespace is\Helpers;

class Matches
{
    /**
     * Функция задействует сравнение, приводя данные к строке
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @return void
     */
    public static function equal($haystack, $needle)
    {
        return (string) $haystack === (string) $needle ? true : null;
    }

    /**
     * Функция проверяет наличие строки
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @return void
     */
    public static function string($haystack, $needle)
    {
        return Strings::match($haystack, $needle);
    }

    /**
     * Функция проверяет наличие строки по регулярному выражению
     *
     * @param [type] $haystack
     * @param [type] $regexp
     * @return void
     */
    public static function regexp($haystack, $regexp)
    {
        return preg_match('/' . $regexp . '/u', $haystack);
    }

    /**
     * Функция проверяет наличие строки по маске
     *
     * @param [type] $haystack
     * @param [type] $mask
     * @return void
     */
    public static function mask($haystack, $mask)
    {
        return self::regexp($haystack, self::maskToRegexp($mask));
    }

    /**
     * Функция сравнивает число в диапазоне от мин до макс включительно
     * если мин/макс не заданы, то считаются минус/плюс бесконечностью
     *
     * @param [type] $haystack
     * @param [type] $min
     * @param [type] $max
     * @return void
     */
    public static function numeric($haystack, $min = null, $max = null)
    {
        $haystack = Prepare::numeric($haystack);
        $min = System::set($min) ? (float) $min : false;
        $max = System::set($max) ? (float) $max : false;

        $rmin = $min === false ? true : $haystack >= $min;
        $rmax = $max === false ? true : $haystack <= $max;

        return $rmin && $rmax ? true : null;
    }

    /**
     * Функция задействует сравнение, приводя данные к строке
     * сравнение идет с массивом haystack
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $and
     * @return void
     */
    public static function equalIn($haystack, $needle, $and = null)
    {
        $result = null;

        foreach ($haystack as $item) {
            $result = self::equal($item, $needle);
            if (
                ($and && !$result)
                || (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);

        return $result;
    }

    /**
     * Функция проверяет наличие строки
     * сравнение идет с массивом haystack
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $and
     * @return void
     */
    public static function stringIn($haystack, $needle, $and = null)
    {
        $result = null;
        foreach ($haystack as $item) {
            $result = self::string($item, $needle);
            if (
                ($and && !$result)
                || (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);
        return $result;
    }

    /**
     * Функция проверяет наличие строки по регулярному выражению
     * сравнение идет с массивом haystack
     *
     * @param [type] $haystack
     * @param [type] $regexp
     * @param [type] $and
     * @return void
     */
    public static function regexpIn($haystack, $regexp, $and = null)
    {
        $result = null;
        foreach ($haystack as $item) {
            $result = self::regexp($item, $regexp);
            if (
                ($and && !$result)
                || (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);
        return $result;
    }

    /**
     * Функция проверяет наличие строки по маске
     * сравнение идет с массивом haystack
     *
     * @param [type] $haystack
     * @param [type] $mask
     * @param [type] $and
     * @return void
     */
    public static function maskIn($haystack, $mask, $and = null)
    {
        return self::regexpIn($haystack, self::maskToRegexp($mask));
    }

    /**
     * Функция сравнивает число в диапазоне от мин до макс включительно
     * если мин/макс не заданы, то считаются минус/плюс бесконечностью
     * сравнение идет с массивом haystack
     *
     * @param [type] $haystack
     * @param [type] $min
     * @param [type] $max
     * @param [type] $and
     * @return void
     */
    public static function numericIn($haystack, $min = null, $max = null, $and = null)
    {
        $result = null;
        foreach ($haystack as $item) {
            $result = self::numeric($item, $min, $max);
            if (
                ($and && !$result)
                || (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);
        return $result;
    }

    /**
     * Функция задействует сравнение, приводя данные к строке
     * сравнение идет по массиву needle
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $and
     * @return void
     */
    public static function equalOf($haystack, $needle, $and = null)
    {
        return self::equalIn($needle, $haystack, $and);
    }

    /**
     * Функция проверяет наличие строки
     * сравнение идет по массиву needle
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $and
     * @return void
     */
    public static function stringOf($haystack, $needle, $and = null)
    {
        return self::stringIn($needle, $haystack, $and);
    }

    /**
     * Функция проверяет наличие строки по регулярному выражению
     * сравнение идет по массиву регулярных выражений regexp
     *
     * @param [type] $haystack
     * @param [type] $regexp
     * @param [type] $and
     * @return void
     */
    public static function regexpOf($haystack, $regexp, $and = null)
    {
        $result = null;
        foreach ($regexp as $item) {
            $result = self::regexp($haystack, $item);
            if (
                ($and && !$result)
                || (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);
        return $result;
    }

    /**
     * Функция проверяет наличие строки по маске
     * сравнение идет по массиву масок mask
     *
     * @param [type] $haystack
     * @param [type] $mask
     * @param [type] $and
     * @return void
     */
    public static function maskOf($haystack, $mask, $and = null)
    {
        $result = null;
        foreach ($mask as $item) {
            $result = self::mask($haystack, $item);
            if (
                ($and && !$result)
                || (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);
        return $result;
    }

    /**
     * Функция сравнивает число в диапазоне от мин до макс включительно
     * если мин/макс не заданы, то считаются минус/плюс бесконечностью
     * сравнение идет по массиву значений minmax
     *
     * @param [type] $haystack
     * @param [type] $minmax
     * @param [type] $and
     * @return void
     */
    public static function numericOf($haystack, $minmax, $and = null)
    {
        $result = null;
        foreach ($minmax as $item) {
            $result = self::numeric($haystack, Objects::first($item, 'value'), Objects::last($item, 'value'));
            if (
                ($and && !$result)
                || (!$and && $result)
            ) {
                break;
            }
        }
        unset($item);
        return $result;
    }

    /**
     * Функция преобразования маски в регулярное выражение
     *
     * @param [type] $mask
     * @return void
     */
    public static function maskToRegexp($mask)
    {
        return $mask = '^' . str_replace(['\*', '\?'], ['.*', '.'], preg_quote($mask)) . '$';
    }
}
