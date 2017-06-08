<?php

class session_apt
{
    protected $options;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    public static function &get_instance($name = 'tea_file', $options = [])
    {
        static $instances = [];
        if (!isset($instances[$name])) {
            $class = $name;
            if (!class_exists($class)) {
                $path = dirname(__FILE__).DIRECTORY_SEPARATOR.$name.'.php';
                if (!file_exists($path)) {
                    debug::error('Session Driver Error', "Session Driver <b>$name</b> could not loaded.");
                }
                require_once $path;
            }
            $instances[$name] = new $class($options);
        }

        return $instances[$name];
    }

    public function register()
    {
        session_set_save_handler([$this, 'open'], [$this, 'close'], [$this, 'read'], [$this, 'write'], [$this, 'destroy'], [$this, 'gc']);
    }
}
