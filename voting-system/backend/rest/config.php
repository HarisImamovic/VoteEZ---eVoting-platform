<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));

class Config
{
    private static $db_config = null;
    public static function DB()
    {
        if (self::$db_config !== null) {
            return self::$db_config;
        }
        self::$db_config = [
            'host' => getenv('DB_HOST'),
            'user' => getenv('DB_USER'),
            'pass' => getenv('DB_PASS'),
            'port' => getenv('DB_PORT') ?: 11999,
            'name' => getenv('DB_NAME'),
            'ssl_ca' => getenv('DB_SSL_CA'),
        ];
        foreach (['host', 'user', 'pass', 'name'] as $key) {
            if (empty(self::$db_config[$key])) {
                throw new Exception("Database config '$key' is missing");
            }
        }
        return self::$db_config;
    }

    // Helper methods for accessing DB config
    public static function DB_HOST()
    {
        $config = self::DB();
        return $config['host'];
    }

    public static function DB_USER()
    {
        $config = self::DB();
        return $config['user'];
    }

    public static function DB_PASS()
    {
        $config = self::DB();
        return $config['pass'];
    }

    public static function DB_PORT()
    {
        $config = self::DB();
        return $config['port'];
    }

    public static function DB_NAME()
    {
        $config = self::DB();
        return $config['name'];
    }

    public static function DB_SSL_CA()
    {
        $config = self::DB();
        return $config['ssl_ca'];
    }

    public static function JWT_SECRET(): string
    {
        $secret = getenv("JWT_SECRET");
        if (!$secret) {
            throw new Exception("JWT_SECRET environment variable is not set!");
        }
        return $secret;
    }

    public static function get_env($name, $default)
    {
        return isset($_ENV[$name]) && trim($_ENV[$name]) != "" ? $_ENV[$name] : $default;
    }
}
