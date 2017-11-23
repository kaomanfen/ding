<?php

namespace Kmf\Ding\Enum;

class Helper
{
    /**
     * 过滤一维、二维关联数组中字段
     * @param array $array
     * @param array $fields
     * @return array
     */
    public static function filterFields(array $array, array $fields)
    {
        if (count($array) == count($array, 1)) {
            foreach ($fields as $val) {
                unset($array[$val]);
            }

            return $array;
        }

        // 二维数组
        foreach ($array as $key => $val) {
            foreach ($fields as $value) {
                unset($array[$key][$value]);
            }
        }

        return $array;
    }

    /**
     * 保留一维、二维关联数组中字段
     * @param array $array
     * @param array $fields
     * @return array
     */
    public static function keepFields(array $array, array $fields)
    {
        $return = array();
        if (count($array) == count($array, 1)) {
            foreach ($fields as $val) {
                isset($array[$val]) && ($return[$val] = $array[$val]);
            }

            return $return;
        }

        // 二维数组
        foreach ($array as $key => $val) {
            foreach ($fields as $value) {
                isset($array[$key][$value]) && ($return[$key][$value] = $array[$key][$value]);
            }
        }

        return $return;
    }

    public static function object2array($object) {
        $object =  json_decode( json_encode( $object),true);
        return  $object;
    }

}