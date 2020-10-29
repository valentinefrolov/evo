<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 03.03.16
 * Time: 14:52
 */

namespace Evo\Helper\View;

use Evo;

class Date
{
    public static function regexp($pattern, $date=null, $flags='')
    {
        if(!$date) {
            $date = time();
        } else if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        preg_match_all('/([a-zA-Z]{1})/', $pattern, $incoming);

        $replacement = [];

        foreach($incoming[0] as $index => $char) {
            if($char == 'D') {$replacement[$index] = 'common.date.'; continue;}// короткий день недели
            if($char == 'l') {$replacement[$index] = 'common.date.'; continue;}// Полный день недели
            if($char == 'M') {$replacement[$index] = 'common.date.'; continue;}// Сокращенный месяц
            if($char == 'F') {$replacement[$index] = $flags == 'p' ? 'common.date_parental.' : 'common.date.'; continue;}// Полный месяц
            if($char == 'a' || $char == 'A') {$replacement[$index] = 'common.date.'; continue;}// Полный месяц
        }

        if(count($replacement)) {
            $marked = preg_replace('/([a-zA-Z]{1})/', '#{$1}#', $pattern);
            $markedFormatted = date($marked, $date);
            preg_match_all('/\#\{([^\}\{\#\#]*)\}\#/', $markedFormatted, $outgoing);
            $toReplace = [];
            foreach($replacement as $index => $address) {
                $toReplace[$outgoing[0][$index]] = '#{'.Evo::app()->lang->t(strtolower($address.$outgoing[1][$index])).'}#';
            }

            $markedFormatted = str_replace(array_keys($toReplace), $toReplace, $markedFormatted);
            return str_replace(['#{', '}#'], '', $markedFormatted);
        } else {
            return date($pattern, $date);
        }
    }

}