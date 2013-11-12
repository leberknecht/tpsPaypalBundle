<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 12.11.13
 * Time: 19:54
 */

namespace tps\PaypalBundle\lib;

class Tools
{
    /**
     * @param array $arr
     * @param $glue
     * @param string $offsetKey
     * @return array
     */
    public static function flatenArray(array $arr, $glue, $offsetKey = '')
    {
        $result = array();
        $iterationKey = '';

        foreach($arr as $key => $value) {
            if (!empty($offsetKey) && empty($iterationKey)) {
               $iterationKey = $offsetKey . $glue;
            }

            if (is_array($arr[$key])) {
                $result = array_merge($result, Tools::flatenArray($arr[$key], $glue, $iterationKey . $key));
            } else {
                $result[$iterationKey . $key] = $value;
            }
        }
        return $result;
    }

    /**
     * @param $array
     * @param $keys
     * @return array
     */
    public static function arraySliceAssoc($array, $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }
} 