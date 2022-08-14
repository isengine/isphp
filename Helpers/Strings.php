<?php

namespace is\Helpers;

class Strings
{
    /**
     * Функция проверки наличия подстроки или символа в заданной строке
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @return void
     */
    public static function match($haystack, $needle)
    {
        return
            !System::set($haystack) || !System::set($needle) || mb_strpos($haystack, $needle) === false
            ? null
            : true;
    }

    /**
     * Функция поиска подстроки или символа в заданной строке
     * если задан pos, то он ищет соответствие подстроки в заданной позиции
     * положительное значение - поиск с начала, от 0
     * отрицательное значение - поиск с конца, от -1
     * если pos не задан, то возвращает первое значение с начала
     * специальное значение pos 'r' задает возврат индекса последнего значения в строке
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $position
     * @return void
     */
    public static function find($haystack, $needle, $position = null)
    {
        $pos = System::set($position);
        $haystack = (string) $haystack;
        if ($pos && $position !== 'r') {
            $result = mb_substr($haystack, $position, mb_strlen($needle));
            return $result === $needle ? true : false;
        } elseif ($position === 'r') {
            return mb_strrpos($haystack, $needle);
        } else {
            return mb_strpos($haystack, $needle);
        }
    }

    /**
     * Функция возвращает подстроку по указанному индексу (позиции) и заданной длины
     * если длина задана, то она смещается
     * положительное значение - вперед
     * отрицательное значение - назад
     * если длина не задана, то возвращается вся строка от индекса и до конца
     * специальное значение position = true задает $length от конца строки
     *
     * например, строка "positionare"
     * 0 :          > positionare
     * 3 :          >    itionare
     * 6 :          >       onare
     * 0, 3 :       > pos
     * 3, 3 :       >    iti
     * 6, 3 :       >       ona
     * 6, -3 :      >     tio
     * -3 :         >         are
     * -6 :         >      ionare
     * -6, 3 :      >      ion
     * -6, -3 :     >    iti
     * 1, 0, true : >  ositionare
     * 1, 1, true : >  ositionar
     * 1, 2, true : >  ositiona
     *
     * @param [type] $haystack
     * @param [type] $index
     * @param [type] $length
     * @param [type] $position
     * @return void
     */
    public static function get($haystack, $index, $length = null, $position = null)
    {
        $haystack = (string) $haystack;
        if (System::set($length) && !$position) {
            if ($length < 0) {
                $idx = $index;
                $index += $length + 1;
                $length = abs($length);
                if ($idx > 0 && $index < 0) {
                    $length = $idx + 1;
                    $index = 0;
                } elseif ($length > self::len($haystack)) {
                    $length = $idx + 1;
                }
            }
            return mb_substr($haystack, $index, $length);
        } elseif ($length && $position) {
            return mb_substr($haystack, $index, 0 - $length);
            //return mb_substr($haystack, 0, $index);
        } else {
            return mb_substr($haystack, $index);
        }
    }

    /**
     * Функция удаления части строки с начала или с конца (при отрицательном значении)
     * умолчание выставлено таким образом, что при многократном вызове функции, строка будет уменьшаться с конца
     * первое значение - индекс, позиция с начала (или с конца при отрицательном значении)
     * второе значение - длина, вперед или назад (при отрицательном значении),
     * если не задано или 0, то вся длина до конца
     * специальное значение position = true задает $length от конца строки
     *
     * например, строка "positionare"
     * 1 : p
     * 3 : pos
     * 6 : positi
     * -1 : positionar
     * -3 : position
     * -6 : posit
     * 0, 1 : ositionare
     * 3, 1 : postionare
     * 6, 1 : positinare
     * 0, 3 : itionare
     * 3, 3 : posonare
     * 6, 3 : positire
     * 6, -3 : posinare
     * -1, 1 : positionar
     * -3, 1 : positionre
     * -6, 1 : positonare
     * -6, 3 : positare
     * -6, -3 : posonare
     * 1, 0, true : p
     * 1, 1, true : pe
     * 1, 2, true : pre
     *
     * @param [type] $haystack
     * @param integer $index
     * @param [type] $length
     * @param [type] $position
     * @return void
     */
    public static function cut($haystack, $index = -1, $length = null, $position = null)
    {
        $len = mb_strlen($haystack);

        if (!$length) {
            $length = $position ? 0 : $len;
        }

        $first = $index < 0 ? $len + $index : $index;
        $last = $first + ($length < 0 ? $length + 1 : $length);

        if ($position) {
            $last = $len - abs($length);
        }

        if ($first > $last) {
            $point = $first + 1;
            $first = $last;
            $last = $point;
            unset($point);
        }

        if ($first < 0) {
            $first = 0;
        } elseif ($first > $len) {
            $first = $len;
        }
        if ($last < 0) {
            $last = 0;
        } elseif ($last > $len) {
            $last = $len;
        }

        return mb_substr($haystack, 0, $first) . mb_substr($haystack, $last);
    }

    /**
     * Функция, которая возвращает подстроку до первого заданного значения
     * включение include позволяет включить в строку найденное значение
     * специальное значение reverse возвращает строку до последнего заданного значения
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $include
     * @param [type] $reverse
     * @return void
     */
    public static function before($haystack, $needle, $include = null, $reverse = null)
    {
        $pos = (int) self::find($haystack, $needle, $reverse ? 'r' : null);

        if (!System::set($pos)) {
            return !$pos ? null : $haystack;
        }

        return self::get($haystack, 0, $include ? $pos + 1 : $pos);
    }

    /**
     * Функция, которая возвращает подстроку после первого заданного значения
     * включение include позволяет включить в строку найденное значение
     * специальное значение reverse возвращает строку после последнего заданного значения
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $include
     * @param [type] $reverse
     * @return void
     */
    public static function after($haystack, $needle, $include = null, $reverse = null)
    {
        $pos = (int) self::find($haystack, $needle, $reverse ? 'r' : null);

        if (!System::set($pos)) {
            return !$pos ? null : $haystack;
        }

        return self::get($haystack, $include ? $pos : $pos + 1);
    }

    /**
     * Функция дополнения строки string на указанное число символов $len
     * символами или подстрокой values
     * последний аргумент reverse заставляет дополнять строку в начало
     *
     * @param [type] $string
     * @param [type] $len
     * @param string $values
     * @param [type] $reverse
     * @return void
     */
    public static function add($string, $len, $values = ' ', $reverse = null)
    {
        return str_pad(
            $string,
            (int) self::len($string) + $len,
            $values,
            $reverse ? STR_PAD_LEFT : STR_PAD_RIGHT
        );
    }

    /**
     * Функция удаления символов из строки по их номерам
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @return void
     */
    public static function remove($haystack, $needle)
    {
        if (System::typeOf($haystack, 'iterable')) {
            return null;
        }

        $needle = Objects::convert($needle);
        $needle = Objects::sort($needle, true);
        $needle = Objects::reverse($needle);

        foreach ((array) $needle as $item) {
            $haystack = substr_replace($haystack, '', $item, 1);
        }
        unset($item);

        return $haystack;
    }

    /**
     * Функция повторяет строку string указанное число раз
     *
     * @param [type] $string
     * @param [type] $count
     * @return void
     */
    public static function multiply($string, $count)
    {
        $result = null;

        if (
            !System::set($string)
            || !System::type($count, 'numeric')
        ) {
            return $string;
        }

        while ((int) $count > 0) {
            $result .= $string;
            $count--;
        }

        return $result;
    }

    /**
     * Функция разворота строки задом наперед
     *
     * @param [type] $item
     * @return void
     */
    public static function reverse($item)
    {
        $item = mb_convert_encoding($item, 'UTF-16LE', 'UTF-8');
        $item = strrev($item);
        return mb_convert_encoding($item, 'UTF-8', 'UTF-16BE');
    }

    /**
     * Функция возврата первого символа строки
     *
     * @param [type] $item
     * @return void
     */
    public static function first($item)
    {
        if (!$item) {
            return;
        }
        $item = (string) $item;
        return $item[0];
    }

    /**
     * Функция возврата последнего символа строки
     *
     * @param [type] $item
     * @return void
     */
    public static function last($item)
    {
        if (!$item) {
            return;
        }

        return mb_substr($item, -1);
    }

    /**
     * Функция замены первого символа строки
     *
     * @param [type] $item
     * @param [type] $data
     * @return void
     */
    public static function refirst(&$item, $data)
    {
        if (!$item) {
            return;
        }

        $item = $data . self::unfirst($item);
        return $item;
    }

    /**
     * Функция замены последнего символа строки
     *
     * @param [type] $item
     * @param [type] $data
     * @return void
     */
    public static function relast(&$item, $data)
    {
        if (!$item) {
            return;
        }

        $item = self::unlast($item) . $data;
        return $item;
    }

    /**
     * Функция возврата первого символа строки
     *
     * @param [type] $item
     * @return void
     */
    public static function unfirst($item)
    {
        return System::set($item) ? mb_substr($item, 1) : '';
    }

    /**
     * Функция возврата последнего символа строки
     *
     * @param [type] $item
     * @return void
     */
    public static function unlast($item)
    {
        return System::set($item) ? mb_substr($item, 0, -1) : null;
    }

    /**
     * Функция возврата длины строки
     *
     * @param [type] $item
     * @return void
     */
    public static function len($item)
    {
        return mb_strlen($item);
    }

    /**
     * Функция разбивает строку на массив данных по указанным символам
     *
     * @param string $item
     * @param string $splitter
     * @return void
     */
    public static function split($item = '', $splitter = '\s,;', $clear = null)
    {
        if (System::typeOf($item) !== 'scalar') {
            return null;
        } elseif (System::type($splitter) !== 'string') {
            return [$item];
        }

        $result = preg_split('/[' . $splitter . ']/u', $item, 0, 0);

        if (System::set($clear)) {
            $result = Objects::clear($result);
            //$result = array_diff($result, [null]);
        }

        return $result;
        //return preg_split('/[' . $splitter . ']/u', $item, null, System::set($clear) ? PREG_SPLIT_NO_EMPTY : null);
    }

    /**
     * Функция объединяет массив в строку с разделителем
     *
     * @param [type] $item
     * @param string $splitter
     * @return void
     */
    public static function join($item, $splitter = ' ')
    {
        $type = System::type($item);

        if ($type !== 'array' && $type !== 'object') {
            return $item;
        }

        if ($type === 'object') {
            $item = (array) $item;
        }

        return implode($splitter, $item);
    }

    /**
     * Функция объединяет массив в строку с разделителями
     * можно указать разделители между ключами, между значениями
     * первую и последную строки, которые будут добавлены только если результат не будет пустым
     *
     * @param [type] $item
     * @param [type] $keys
     * @param [type] $values
     * @param [type] $first
     * @param [type] $last
     * @return void
     */
    public static function combine($item, $keys = null, $values = null, $first = null, $last = null)
    {
        if (!System::typeIterable($item)) {
            return $item;
        }

        $result = null;

        $f = Objects::first($item, 'key');

        foreach ($item as $k => $i) {
            $result .= ($k === $f ? null : $keys) . $k . $values . $i;
        }
        unset($k, $i);

        return $result ? $first . $result . $last : null;
    }

    /**
     * Функция объединяет массив в строку по маске {k} {i}
     * except содержит символы-исключения, которые будут очищены из массива
     *
     * @param [type] $item
     * @param [type] $mask
     * @param [type] $first
     * @param [type] $last
     * @param [type] $except
     * @return void
     */
    public static function combineMask($item, $mask, $first = null, $last = null, $except = null)
    {
        if (!System::typeIterable($item)) {
            return $except ? self::except($item, $except) : $item;
        }

        $result = $first;

        foreach ($item as $k => $i) {
            if ($except) {
                $k = self::except($k, $except);
                $i = self::except($i, $except);
            }
            $result .= self::replace($mask, ['{k}', '{i}'], [$k, $i]);
        }
        unset($k, $i);

        return $result . $last;
    }

    /**
     * Функция очистки строки от указанных символов
     *
     * @param [type] $item
     * @param [type] $except
     * @return void
     */
    public static function except($item, $except = null)
    {
        if (
            !System::set($item)
            || !System::set($except)
        ) {
            return $item;
        }

        return preg_replace('/[' . preg_quote($except, '/') . ']/u', '', $item);
    }

    /**
     * Функция замены search на replace в строке item
     * поддерживает массив замен, как в оригинальной функции на php так и в js реализации
     *
     * @param [type] $item
     * @param [type] $search
     * @param string $replace
     * @return void
     */
    public static function replace($item, $search, $replace = '')
    {
        return System::set($item) ? str_replace($search, $replace, $item) : null;
    }

    /**
     * Функция удаления всех пробелов и пустых символов из строки
     *
     * @param [type] $item
     * @return void
     */
    public static function clear($item)
    {
        return preg_replace('/(\s|\r|\n|\r\n)+/u', '', $item);
    }

    /**
     * Функция удаления одинаковых значений из строки
     *
     * @param [type] $item
     * @return void
     */
    public static function unique($item)
    {
        $result = preg_split('//u', $item);
        $result = array_unique($result);
        $result = implode('', $result);

        return $result;
    }

    /**
     * Функция сортировки строки по символам
     * вторым аргументом можно выключить сортировку с учетом регистра
     *
     * @param [type] $haystack
     * @param boolean $register
     * @return void
     */
    public static function sort($haystack, $register = true)
    {
        $haystack = preg_split('//u', $haystack);

        sort($haystack, $register ? SORT_NATURAL : SORT_NATURAL | SORT_FLAG_CASE);

        $str = implode('', $haystack);

        //foreach ($haystack as $item) {
        //    $str .= $item;
        //}
        //unset($item);

        return $str;
    }

    /**
     * Функция сортировки строки в случайном порядке
     *
     * @param [type] $haystack
     * @return void
     */
    public static function random($haystack)
    {
        $haystack = preg_split('//u', $haystack);
        shuffle($haystack);
        return self::join($haystack, null);
    }

    /**
     * Функция возвращает строку, содержащую различия между двумя строками
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @return void
     */
    public static function difference($haystack, $needle)
    {
        $haystack = preg_split('//u', $haystack);
        $needle = preg_split('//u', $needle);

        $diff = array_diff($haystack, $needle);

        $str = null;

        if (!empty($diff)) {
            foreach ($diff as $item) {
                $str .= $item;
            }
            unset($item);
        }

        return $str;
    }

    /**
     * Функция, которая разбивает строку на значения до сплиттера и после сплиттера
     * и возвращает в виде массива
     * сплиттер вырезается из строки
     *
     * @param [type] $string
     * @param string $splitter
     * @return void
     */
    public static function pairs($string, $splitter = ':')
    {
        $pos = (int) self::find($string, $splitter);

        return [
            self::get($string, 0, $pos),
            self::get($string, $pos + 1)
        ];
    }

    /**
     * Функция, которая разбивает строку на значения до индекса и после индекса
     * и возвращает в виде массива
     * индекс вырезается из строки, но
     * можно задать смещение, и тогда индекс останется либо в строке после (1), либо в строке до (-1)
     *
     * @param [type] $string
     * @param [type] $index
     * @param [type] $offset
     * @return void
     */
    public static function pairsByIndex($string, $index, $offset = null)
    {
        $before = $offset < 0 ? 1 : 0;
        $after = $offset > 0 ? 0 : 1;

        return [
            self::get($string, 0, $index + $before),
            self::get($string, $index + $after)
        ];
    }
}
