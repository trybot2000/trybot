<?php

namespace App\Helpers;

/**
 *    Various helpers, registered  as an alias in config/app.php
 *    
 *    These are used in any other file as Helper::method($foo)
 */
class Helper
{
    public static function r($min = 111111, $max = 9999999)
    {
        return random_int($min, $max);
    }

    public function implodeNice($array,$ending = "and")
    {
        $return = "";

        if (!is_array($array)) {
            return $return;
        }

        $countOriginal = count($array);

        if ($countOriginal == 1) {
            return array_shift($array);
        }

        if ($countOriginal == 2) {
            return array_shift($array) . $ending . " " . array_shift($array);
        }

        for ($i = 0; $i < $countOriginal; $i++) {
            if (count($array) >= 2) {
                $return .= array_shift($array) . ", ";
            } else
            if (count($array) == 1) {
                $return .= $ending . " " . array_shift($array);
            }
        }

        return $return;
    }
}
