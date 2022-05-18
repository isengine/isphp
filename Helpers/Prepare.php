<?php

namespace is\Helpers;

class Prepare
{
    /*
    *  Класс, который производит очистку данных по заданному параметру
    *  например, перед передачей ее для записи в базу данных
    *  на входе нужно указать значение $data и тип преобразования $type
    *
    *  параметры типов (если нужно несколько, можно перечислять через пробел и/или запятую):
    *    format - оставление в строке только (!) цифр, латинских букв, пробелов
    *    и разрешенных знаков для передачи данных в формате системы
    *    alphanumeric - оставление в строке только (!) цифр, латинских букв и пробелов
    *    numeric - оставление в строке только (!) цифр
    *    datetime - оставление в строке только (!) цифр и знаков, встречающихся в формате даты и времени
    *    phone - приведение строки к телефонному номеру
    *    phone_ru - приведение строки к телефонному номеру россии (+7 заменяется на 8)
    *    phone_link - приведение строки к телефонной ссылке (добавляется + вначале)
    *    login/email - приведение строки к формату логина/email
    *    url - приведение строки к формату url, включая спецсимволы
    *    simpleurl - приведение строки к формату url без спецсимволов, с обрезкой всех параметров
    *    urlencode - приведение строки к формату url, в котором символы кодируются % и hex-кодом
    *    urldecode - приведение строки из формата urlencode в обычный текстовый вид
    *    leavespaces - укажите, чтобы оставить по одному пробелу (если они вообще есть) в начале и в конце сторки
    *    tospaces - приведение всех пробелов, табуляций и символов пробелов к одному пробелу
    *    nospaces - удаление всех пробелов
    *    codespaces - удаление незначащих для кода пробелов, сокращение кода
    *    onestring - приведение данных к однострочному виду
    *    code - htmlspecialchars
    *    htmlencode - htmlentities
    *    htmldecode - html_entity_decode
    *    notags - удаление всех тегов
    *    cleartags - очищение всех атрибутов внутри тегов
    *    tags - удаление всех тегов, кроме разрешенных
    *      чтобы этот параметр работал корректно, входящие данные должны быть кодированы
    *      htmlspecialchars, в противном случае теги будут очищены
    *      на предварительном этапе обработки
    *
    *  теперь, если указать третий параметр 'false', то чистка тегов будет пропущена
    *  т.е. все теги в тексте останутся как есть
    *  если указать 'true', то будут оставлены только теги по-умолчанию
    *  если же задать массив, то будут исключены все теги, кроме указанных
    *  действие этого параметра не распространяется на код php и скрипты, т.к. они будут очищены в любом случае
    *
    *  в функцию добавился четвертый параметр, который может быть как массивом, так и иметь значение true
    *  в качестве массива он может содержать ключи 'minlen', 'maxlen', 'minnum', 'maxnum' и 'match',
    *  по которым будет идти проверка входной строки, объект же будет преобразован в массив
    *  также данный параметр имеет и еще одно свойство: если этот параметр не пустой
    *  (например, строка или число, хотя мы настоятельно рекомендуем указывать массив или 'true'),
    *  окончательная, очищенная строка будет сравниваться с исходной
    *  в случае совпадения будет возвращаться очищенная строка, в противном случае - false
    *  таким образом, эта функция теперь объединяет в себе очищение и проверку
    *
    *  также нельзя считать эту функцию полностью безопасной, т.к. она не очистит обфусцированные и шифрованные данные
    *  (т.е. переданные фрагментами), например: '<scr ipt>' или 'PHNjcmlwdD4=' (base64)
    *  однако мы стараемся сделать так, чтобы все файлы системы проходили антивирусную проверку,
    *  в частности через AIBolit, и не выдавать даже подозрений на вирусы,
    *  так чтобы вредоносный код можно было сразу же обнаружить
    *
    *  на выходе отдает преобразованное значение $data
    */

    public static function clear($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = self::script($data);
        $data = self::stripTags($data);
        $data = self::trim($data);
        $data = self::spaces($data);

        return $data;
    }

    /**
     * Выполняем предварительное очищение - от скриптов, программного кода
     *
     * @param [type] $data
     * @return void
     */
    public static function script($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/<\?.+?\?>/u', '', $data);
        $data = preg_replace('/<script.+?\/script>/ui', '', $data);

        return $data;
    }

    /**
     * Продолжаем предварительное очищение - от всех тегов, кроме разрешенных
     *
     * @param [type] $data
     * @param [type] $tags
     * @return void
     */
    public static function stripTags($data, $tags = null)
    {
        if (!System::set($data)) {
            return $data;
        }

        // задаем разрешенные теги
        if (empty($tags)) {
            $tags = [
                // base elements
                'a', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'span', 'font', 'br', 'hr', 'img',
                // base formatting
                'b', 'i', 's', 'u', 'blockquote', 'code', 'del', 'em', 'ins', 'small', 'strong', 'sub', 'sup',
                // list
                'ul', 'ol', 'li', 'dl', 'dt', 'dd', 'details', 'summary',
                // table
                'table', 'thead', 'tbody', 'tfoot', 'th', 'tr', 'td', 'col', 'colgroup', 'caption',
                // additional
                'abbr', 'bdi', 'bdo', 'cite', 'dfn', 'kbd', 'mark', 'q', 'rp', 'rt', 'rtc', 'ruby', 'samp', 'var', 'wbr'
            ];
        } else {
            $tags = Objects::convert($tags);
        }

        // подготавливаем список
        $strip = '';
        foreach ($tags as $tag) {
            $strip .= '<' . $tag . '>';
        }

        // завершаем
        unset($tags, $tag);

        // очищаем
        $data = strip_tags($data, $strip);

        return $data;
    }

    /**
     * Убираем все пробелы и табуляцию в начале и в конце
     * второй параметр задает замену при очищении
     * по-умолчанию, пусто
     * можно указать '$1' для замены на найденное значение
     *
     * @param [type] $data
     * @param string $replace
     * @return void
     */
    public static function trim($data, $replace = '')
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/^(\s|(&nbsp;))+/ui', $replace, $data);
        $data = preg_replace('/(\s|(&nbsp;))+$/ui', $replace, $data);

        return $data;
    }

    /**
     * Убираем двойные пробелы и табуляцию
     * второй параметр задает замену при очищении
     * по-умолчанию, '$1' - замена на найденное значение
     * можно указать null для очищения (nospaces) или ' ' для замены на пробел (tospaces)
     *
     * @param [type] $data
     * @param string $replace
     * @return void
     */
    public static function spaces($data, $replace = '$1')
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/(\s|&nbsp;)+/ui', $replace, $data);

        return $data;
    }

    /**
     * Убираем комментарии
     *
     * @param [type] $data
     * @return void
     */
    public static function comments($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        // clear comments [//...] from json parse with frotect of 'href://' string
        $data = preg_replace('/([^\:\"\'])\s*?\/\/.*?([$\r\n])/u', '$1$2', $data);
        // clear comments [/*...*/]
        $data = preg_replace('/\/\*.*?\*\//u', '', $data);
        // clear comments [<!--...-->]
        $data = preg_replace('/(\<\!\-\-).*?(\-\-\>)/u', '', $data);

        return $data;
    }

    /**
     * Оставляем в строке только цифры, латинские буквы, пробелы
     * и разрешенные знаки для передачи данных в формате системы
     *
     * @param [type] $data
     * @return void
     */
    public static function format($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/[^a-zA-Z0-9_\- .,:;]/u', '', $data);
        return $data;
    }

    /**
     * Оставляем в строке только буквы
     *
     * @param [type] $data
     * @return void
     */
    public static function letters($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/[^\w]|\d/u', '', $data);
        return $data;
    }

    /**
     * Оставляем в строке только буквы и пробелы
     *
     * @param [type] $data
     * @return void
     */
    public static function words($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/[^\w ]|\d/u', '', $data);
        return $data;
    }

    /**
     * Оставляем в строке только цифры, буквы и некоторые знаки препинания
     *
     * @param [type] $data
     * @param string $replace
     * @param boolean $compact
     * @return void
     */
    public static function alphanumeric($data, $replace = '', $compact = true)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/[^0-9_\w\-\.\, ]/u', $replace, $data);
        //$data = preg_replace('/[^a-zA-Z0-9_\- ]/u', $replace, $data);
        if ($compact && $replace) {
            $data = preg_replace('/\\' . $replace . '{2,}/u', $replace, $data);
        }
        return $data;
    }

    /**
     * Оставляем в строке только цифры
     *
     * @param [type] $data
     * @return void
     */
    public static function numeric($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = str_replace(',', '.', $data);
        $data = preg_replace('/[^\d]/ui', '', $data);
        //$data = preg_replace('/^[^\d]+?(\d)/', '$1', $data);
        //$data = str_replace(',', '.', $data);
        //$data = (float) $data;
        return $data;
    }

    /**
     * Оставляем в строке только символы в формате даты
     *
     * @param [type] $data
     * @return void
     */
    public static function datetime($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/[^0-9_\-.,:()\\\\\/ ]/u', '', $data);
        return $data;
    }

    /**
     * Оставляем в строке только символы в формате номера телефона
     *
     * @param [type] $data
     * @param [type] $locale
     * @return void
     */
    public static function phone($data, $locale = null)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/[^0-9*#]/u', '', $data);
        //$original = !empty($special) ? $data : null;

        if ($locale === 'ru') {
            $first = mb_substr($data, 0, 1);
            if (strlen($data) == 10) {
                $data = substr_replace($data, '7', 0, 0);
                //$original = !empty($special) ? $data : null;
            } elseif ($first == 8) {
                $data = substr_replace($data, '7', 0, 1);
            }
        }

        return '+' . $data;
    }

    /**
     * Оставляем в строке только символы для логина/email
     *
     * @param [type] $data
     * @return void
     */
    public static function email($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/[^a-zA-Z0-9\-_.@]/u', '', $data);
        return $data;
    }

    /**
     * Синоним предыдущей функции
     *
     * @param [type] $data
     * @return void
     */
    public static function login($data)
    {
        return self::email($data);
    }

    /**
     * Оставляем в строке только символы в формате url
     *
     * @param [type] $data
     * @return void
     */
    public static function url($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/[^a-zA-Z0-9\-_.:\/?&\'\"=#+]/u', '', $data);
        $data = rawurlencode($data);
        return $data;
    }

    /**
     * Оставляем в строке только символы в формате url без спецсимволов
     *
     * @param [type] $data
     * @return void
     */
    public static function simpleurl($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = preg_replace('/[?&].*$/u', '', $data);
        $data = preg_replace('/[^a-zA-Z0-9\-_.:\/\w]/u', '', $data);
        $data = htmlspecialchars($data);
        return $data;
    }

    /**
     * Кодируем строку в формат raw
     * Спецсимволы заменяются % и hex-кодом
     *
     * @param [type] $data
     * @param [type] $except
     * @return void
     */
    public static function urlencode($data, $except = null)
    {
        if (!System::set($data)) {
            return $data;
        }

        if (!$except) {
            return rawurlencode($data);
        } else {
            $data = preg_split('//u', $data);
            $result = null;
            foreach ($data as $item) {
                $result .= !$item || Strings::match($except, $item) ? $item : rawurlencode($item);
            }
            unset($item);
            return $result;
        }
    }

    /**
     * Декодируем строку из формата raw
     *
     * @param [type] $data
     * @return void
     */
    public static function urldecode($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = rawurldecode($data);
        //$data = preg_replace('/[^a-zA-Z0-9\-_.,:\/?&=#+\w ]/u', '', $data);
        return $data;
    }

    /**
     * Приводим весь текст в одну строку
     *
     * @param [type] $data
     * @return void
     */
    public static function onestring($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        // clear line breaks from json prepare: vvv
        // $data = preg_replace('/\r\n\s*|\r\s*|\n\s*/u', '', $data);
        $data = preg_replace('/([^\s]|^)[\s]*(\r?\n){1,}[\s]*([^\s]|$)/u', '$1 $3', $data);
        return $data;
    }

    /**
     * Приводим весь текст в вид, когда можно печатать код
     * все спецсимволы кодируются в html-сущности
     *
     * @param [type] $data
     * @return void
     */
    public static function code($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5);
        return $data;
    }

    /**
     * Кодируем строку в формат html-сущностей
     *
     * @param [type] $data
     * @return void
     */
    public static function htmlencode($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = htmlentities($data);
        return $data;
    }

    /**
     * Декодируем строку из формата html-сущностей
     *
     * @param [type] $data
     * @return void
     */
    public static function htmldecode($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = html_entity_decode($data);
        return $data;
    }

    /**
     * Удаление всех тегов, кроме разрешенных
     *
     * @param [type] $data
     * @param [type] $striptags
     * @return void
     */
    public static function tags($data, $striptags = null)
    {
        if (!System::set($data)) {
            return $data;
        }

        $data = htmlspecialchars_decode($data);
        $data = strip_tags($data, $striptags);
        return $data;
    }

    /**
     * Удаление всех тегов
     * второй параметр задает замену при очищении
     * по-умолчанию, null - замена на пустое значение
     * можно указать ' ' для замены на пробел (notagsspaced)
     *
     * @param [type] $data
     * @param string $replace
     * @return void
     */
    public static function notags($data, $replace = '')
    {
        if (!System::set($data)) {
            return $data;
        }

        //$data = preg_replace('/([^\s\t]|^)[\s\t]*(\r?\n){1,}[\s\t]*([^\s\t]|$)/', '$1 $3', $data);
        $data = preg_replace('/(<\/\w+?>)|(<\w+?\s.+?>)|(<\w+?>)/u', $replace, $data);

        return $data;
    }

    /**
     * Очищение всех атрибутов внутри тегов
     *
     * @param [type] $data
     * @return void
     */
    public static function cleartags($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        //$data = preg_replace('/([^\s\t]|^)[\s\t]*(\r?\n){1,}[\s\t]*([^\s\t]|$)/', '$1 $3', $data);
        $data = preg_replace('/<(\w+)?\s.+?>/u', '<$1>', $data);
        return $data;
    }

    /**
     * Ограничиваем длину строки
     *
     * @param [type] $data
     * @param [type] $min
     * @param [type] $max
     * @return void
     */
    public static function len($data, $min = null, $max = null)
    {
        // раньше было specail minlen/maxlen
        // сравнение original/minnum/maxnum теперь через класс match
        if (!System::set($data)) {
            return $data;
        }

        // правило, задающее минимальную длину строки

        if (
            !empty($min)
            && is_numeric($min)
            && $min > 0
            && mb_strlen($data) < $min
        ) {
            $data = null;
        }

        // правило, задающее максимальную длину строки

        if (
            !empty($max)
            && is_numeric($max)
            && $max > 0
            && mb_strlen($data) > $max
        ) {
            $data = mb_substr($data, 0, $max);
        }

        return $data;
    }

    /**
     * Переводим строку в верхний регистр
     *
     * @param [type] $data
     * @return void
     */
    public static function upper($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        return mb_convert_case($data, MB_CASE_UPPER);
    }

    /**
     * Переводим строку в нижний регистр
     *
     * @param [type] $data
     * @return void
     */
    public static function lower($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        return mb_convert_case($data, MB_CASE_LOWER);
    }

    /**
     * Переводим первый символ строки в верхний регистр
     *
     * @param [type] $data
     * @return void
     */
    public static function upperFirst($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        return mb_convert_case(mb_substr($data, 0, 1), MB_CASE_UPPER)
            . mb_convert_case(mb_substr($data, 1), MB_CASE_LOWER);
    }

    /**
     * Переводим каждый первый символ каждого слова в верхний регистр
     *
     * @param [type] $data
     * @return void
     */
    public static function upperEach($data)
    {
        if (!System::set($data)) {
            return $data;
        }

        return mb_convert_case($data, MB_CASE_TITLE);
    }

    /**
     * Функция которая кодирует данные
     * на входе нужно указать исходную строку
     * на выходе отдает готовую строку
     * кодирование происходит в формат base64
     * Теперь функция кодирует и данные в формате json
     *
     * @param [type] $str
     * @return void
     */
    public static function encode($str)
    {
        $type = System::typeOf($str);

        if ($type === 'iterable') {
            $str = Parser::toJson($str);
        } elseif ($type !== 'scalar') {
            return null;
        }

        //if (!System::typeOf($str, 'scalar')) {
        //    return null;
        //}

        $len = (int) Strings::len($str) % 3;
        if ($len) {
            $str = Strings::add($str, 3 - $len);
        }

        return base64_encode($str);
    }

    /**
     * Функция которая декодирует данные
     * на входе нужно указать исходную строку
     * на выходе отдает готовую строку
     * декодирование происходит из формата base64
     * Теперь функция декодирует и данные в формате json
     *
     * @param [type] $str
     * @return void
     */
    public static function decode($str)
    {
        if (!System::typeOf($str, 'scalar')) {
            return null;
        }

        $result = base64_decode($str);

        if (System::typeOf($result, 'iterable')) {
            $result = Parser::fromJson($result);
        }

        return $result;
    }

    /**
     * Функция которая делает хэш данных
     * на входе нужно указать исходную строку
     * на выходе отдает готовую строку
     *
     * @param [type] $str
     * @return void
     */
    public static function hash($str)
    {
        if (!System::typeOf($str, 'scalar')) {
            return null;
        }

        return md5($str);
    }

    /**
     * Функция которая проверяет,
     * соответствует ли переданная строка своему хэшу
     * на входе нужно указать исходную строку и ее хэш
     * на выходе отдает результат проверки
     *
     * @param [type] $str
     * @param [type] $hash
     * @return void
     */
    public static function matchHash($str, $hash)
    {
        if (!System::typeOf($str, 'scalar')) {
            return null;
        }

        return md5($str) === $hash;
    }

    /**
     * Функция которая делает необратимое шифрование данных
     * на входе нужно указать исходную строку
     * на выходе отдает готовую строку
     *
     * @param [type] $str
     * @return void
     */
    public static function crypt($str)
    {
        if (!System::typeOf($str, 'scalar')) {
            return null;
        }

        return password_hash($str, PASSWORD_DEFAULT);
    }

    /**
     * Функция которая проверяет,
     * соответствует ли переданная строка своему хэшу в необратимом шифровании
     * на входе нужно указать исходную строку и ее хэш
     * на выходе отдает результат проверки
     *
     * @param [type] $str
     * @param [type] $hash
     * @return void
     */
    public static function matchCrypt($str, $hash)
    {
        if (!System::typeOf($str, 'scalar')) {
            return null;
        }

        return password_verify($str, $hash);
    }

    /**
     *  Функция которая преобразует строку в объект
     *  Является синонимом Objects::convert
     *  Теперь в нее добавлен второй аргумент,
     *  позволяющий ограничить число элементов в массиве
     *  положительное значение - от начала,
     *  отрицательное значение - от конца
     *
     * @param [type] $data
     * @param [type] $num
     * @return void
     */
    public static function toObject($data, $num = null)
    {
        $data = Objects::convert($data);

        if ($num > 0) {
            $data = Objects::get($data, 0, $num);
        } elseif ($num < 0) {
            $data = Objects::get($data, 0 - $num, 0, true);
        }

        return $data;
    }

    /**
     * Функция которая преобразует объект в строку
     * Является синонимом Strings::join
     *
     * @param [type] $data
     * @param string $replace
     * @return void
     */
    public static function toString($data, $replace = ':')
    {
        return Strings::join($data, $replace);
    }

    /**
     * Функция которая преобразует объект в json
     * Является синонимом Parser::toJson
     *
     * @param [type] $data
     * @return void
     */
    public static function toJson($data)
    {
        return Parser::toJson($data);
    }

    /**
     * Функция которая делает замену в строке
     * Является синонимом Strings::replace
     *
     * @param [type] $data
     * @param [type] $search
     * @param string $replace
     * @return void
     */
    public static function replace($data, $search, $replace = '')
    {
        if (!System::typeOf($data, 'scalar')) {
            return null;
        }

        return Strings::replace($data, $search, $replace);
    }
}
