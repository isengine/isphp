<?php

namespace is\Parents;

class Singleton extends Data
{
    private static $instances = [];

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    public static function getInstance()
    {
        // Здесь мы используем ключевое слово 'static' вместо имени класса.
        // Оно означает 'имя текущего класса'. Эта особенность важна,
        // потому что, когда метод вызывается в подклассе, мы хотим, чтобы
        // экземпляр этого подкласса был создан здесь.

        $subclass = static::class;
        if (!isset(self::$instances[$subclass])) {
            self::$instances[$subclass] = new static();
        }
        return self::$instances[$subclass];
    }
}

// Потребляющий код.
//$database = DatabaseConnection::getInstance();
//$result   = $database->query( $query );
