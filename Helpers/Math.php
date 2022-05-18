<?php

namespace is\Helpers;

class Math
{
    /**
     * Функция получения случайного num-значного числа
     *
     * @param integer $num
     * @return void
     */
    public static function random($num = 4)
    {
        $a = 1 . Strings::multiply('0', $num - 1);
        $b = Strings::multiply('9', $num);

        return rand((int) $a, (int) $b);
    }

    /**
     * Преобразование в правильные числа
     *
     * @param [type] $a
     * @return void
     */
    public static function convert($a)
    {
        return System::typeTo(Strings::replace($a, ',', '.'), 'numeric');
    }

    /**
     * Функция разбора числа
     *
     * @param [type] $a
     * @return void
     */
    public static function fraction($a)
    {
        $a = self::convert($a);
        $split = Strings::split($a, '.');
        return [
            'original' => $a,
            'int' => $split[0],
            'fract' => $split[1],
            'dec' => Strings::len($split[1]),
            'full' => Strings::join($split, null)
        ];
    }

    /**
     * Функция округления чисел
     *
     * @param [type] $a
     * @param integer $precision
     * @param [type] $mode
     * @return void
     */
    public static function precision($a, $precision = 1, $mode = null)
    {
        if (!$a) {
            return 0;
        }

        if (!$precision) {
            $precision = 1;
        }

        if ($mode === 'floor' || $mode === -1 || $mode === '-1') {
            $result = $precision * floor($a / $precision);
        } elseif ($mode === 'ceil' || $mode === 1 || $mode === '1') {
            $result = $precision * ceil($a / $precision);
        } else {
            if (
                $mode !== 'down'
                && $mode !== 'even'
                && $mode !== 'odd'
            ) {
                $mode = 'up';
            }

            $result = $precision * round($a / $precision, 0, constant('PHP_ROUND_HALF_' . strtoupper($mode)));
        }

        return $result == '-0' ? 0 : $result;
    }

    /**
     * Функция точного сложения любых десятичных чисел
     *
     * @param [type] $a
     * @param [type] $b
     * @return void
     */
    public static function add($a, $b)
    {
        return (float) self::convert($a) + (float) self::convert($b);
    }

    /**
     * Функция точного вычитания любых десятичных чисел
     *
     * @param [type] $a
     * @param [type] $b
     * @return void
     */
    public static function sub($a, $b)
    {
        return (float) self::convert($a) - (float) self::convert($b);
    }

    /**
     * Синоним предыдущей функции
     *
     * @param [type] $a
     * @param [type] $b
     * @return void
     */
    public static function diff($a, $b)
    {
        return self::sub($a, $b);
    }

    /**
     * Функция точного умножения любых десятичных чисел
     *
     * @param [type] $a
     * @param [type] $b
     * @return void
     */
    public static function multiply($a, $b)
    {
        return (float) self::convert($a) * (float) self::convert($b);
    }

    /**
     * Функция точного деления любых десятичных чисел
     *
     * @param [type] $a
     * @param [type] $b
     * @return void
     */
    public static function divide($a, $b)
    {
        $a = self::fraction($a);
        $b = self::fraction($b);

        $dec = $a['dec'] - $b['dec'];

        $result = $a['full'] / $b['full'];

        if ($dec) {
            $pow = 10 ** abs($dec);
            $result = $dec > 0 ? $result / $pow : $result * $pow;
        }

        return $result;
    }
}
