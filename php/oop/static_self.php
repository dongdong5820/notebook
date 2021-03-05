<?php
/**
 * Created by PhpStorm.
 * User: sucd01
 * Date: 2021/3/5
 * Time: 14:57
 * desc: static和self的区别
 * static 代表使用的这个类，在父类里写的static，然后被子类覆盖，使用的就是子类的方法和树形
 * self 写在哪个类里面，实际调用的就是哪个类
 */
class Person
{
    public static function name()
    {
        echo "person";
        echo "<br/>";
    }
    public static function callSelf()
    {
        self::name();
    }
    public static function callStatic()
    {
        static::name();
    }
}

class Man extends Person
{
    public static function name()
    {
        echo "man";
        echo "<br/>";
    }
}

Man::callSelf();  // --> person
Man::callStatic(); // --> man