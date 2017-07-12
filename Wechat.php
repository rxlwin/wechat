<?php
/**
 * Created by PhpStorm.
 * User: rxlwin
 * Date: 2017-7-6
 * Time: 13:47
 */

namespace rxlwin\wechat;


class Wechat{
    private static $config=null;
    public function __call($name, $arguments)
    {
        return self::runBase($name, $arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return self::runBase($name, $arguments);
    }

    private static function runBase($name, $arguments){
        return call_user_func_array([new Base(self::$config),$name], $arguments);
    }

    public static function setconfig($config){
        self::$config=$config;
    }
}