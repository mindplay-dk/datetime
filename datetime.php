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
 * @param DateTimeZone|string|null $timezone defaults to default timezone, e.g. datetime()->config->timezone
 *
 * @return DateTimeHelper
 *
 * @throws RuntimeException
 */
function datetime($time = null, $timezone = null)
{
    /**
     * @var DateTimeHelper $helper
     */
    static $helper;

    if (!isset($helper)) {
        $helper = new DateTimeHelper();
    }

    if (is_int($time)) {
        // initialize from UNIX timestamp:
        $helper->time = $time; // (also resets to default timezone)
        return $helper->timezone($timezone); // switch to requested timezone
    }

    if (is_string($time)) {
        // initialize from string:
        return $helper->timezone($timezone)->set($time);
    }

    if ($time === null) {
        // initialize from current time:
        $helper->time = time();
        return $helper->timezone($timezone);
    }

    throw new RuntimeException("invalid argument: " . var_export($time, true));
}
