<?php

namespace is\Helpers;

class Math
{
    public static function random($num = 4)
    {
        // функция получения случайного num-значного числа

        $a = 1 . Strings::multiply('0', $num - 1);
        $b = Strings::multiply('9', $num);

        return rand((int) $a, (int) $b);
    }

    public static function convert($a)
    {
        // преобразование в правильные числа
        return System::typeTo(Strings::replace($a, ',', '.'), 'numeric');
    }

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

    public static function precision($a, $precision = 1, $mode = null)
    {
        // функция округления чисел

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
                $mode !== 'down' &&
                $mode !== 'even' &&
                $mode !== 'odd'
            ) {
                $mode = 'up';
            }

            $result = $precision * round($a / $precision, 0, constant('PHP_ROUND_HALF_' . strtoupper($mode)));
        }

        return $result == '-0' ? 0 : $result;
    }

    public static function add($a, $b)
    {
        // функция точного сложения любых десятичных чисел
        return self::convert($a) + self::convert($b);
    }

    public static function sub($a, $b)
    {
        // функция точного вычитания любых десятичных чисел
        return self::convert($a) - self::convert($b);
    }

    public static function diff($a, $b)
    {
        return self::sub($a, $b);
    }

    public static function multiply($a, $b)
    {
        // функция точного умножения любых десятичных чисел
        return self::convert($a) * self::convert($b);
    }

    public static function divide($a, $b)
    {
        // функция точного деления любых десятичных чисел

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
