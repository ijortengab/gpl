<?php
namespace Gpl\Drupal\Variable;

class VariableManager
{

    protected static $is_modified = false;
    protected static $conf;
    protected static $conf_modified = [];

    /**
     * Mendapatkan variable.
     */
    public static function get($name, $default = null)
    {
        if (self::$conf === null) {
            global $conf;
            self::$conf = $conf;
        }
        return isset(self::$conf[$name]) ? self::$conf[$name] : $default;
    }

    /**
     * Mengeset variable.
     */
    public static function set($name, $value)
    {
        if (self::$conf === null) {
            global $conf;
            self::$conf = $conf;
        }
        self::$is_modified = true;
        self::$conf[$name] = $value;
        self::$conf_modified[$name] = $value;
    }

    /**
     * Menulis variable.
     */
    public static function write()
    {
        if (self::$is_modified) {
            foreach (self::$conf_modified as $name => $value) {
                variable_set($name, $value);
            }
        }
    }
}
