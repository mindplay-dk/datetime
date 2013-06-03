<?php

/**
 * DateTimeHelper tries to make date/time management in PHP less prickly, by
 * providing a chainable helper that can be accessed via a global function.
 *
 * @author Rasmus Schultz
 * @link http://blog.mindplay.dk/
 */

use mindplay\datetime\DateTimeHelper;

/**
 * @param int|string $time integer timestamp or date/time string compatible with strtotime()
 * @return DateTimeHelper
 * @throws RuntimeException
 */
function datetime($time = null)
{
    /**
     * @var DateTimeHelper $helper
     */
    static $helper;

    if (!isset($helper)) {
        $helper = new DateTimeHelper();
    }

    if (is_int($time)) {
        $helper->time = $time;
    } elseif (is_string($time)) {
        $helper->time = strtotime($time);
    } elseif ($time === null) {
        $helper->time = time();
    } else {
        throw new RuntimeException("invalid argument: " . var_export($time, true));
    }

    return $helper;
}
