<?php

namespace mindplay\datetime;

use DateTimeZone;

/**
 * @see DateTimeHelper::$config
 */
class DateTimeConfig
{
    /**
     * @param DateTimeZone|null $timezone default DateTimeZone (or null to use default UTC timezone)
     */
    public function __construct(DateTimeZone $timezone = null)
    {
        $this->timezone = $timezone ?: new DateTimeZone('UTC');
    }

    /**
     * @var DateTimeZone default timezone - applied when calling datetime()
     *
     * @see datetime()
     */
    public $timezone;

    /**
     * @var string[] hash where format-name => date/time format string
     *
     * @see DateTimeHelper::format()
     */
    public $formats = array(
        'default'  => 'n/j/y H:i',
        'datetime' => 'Y-m-d H:i:s',
        'date'     => 'Y-m-d',
        'time'     => 'H:i:s',
        'short'    => 'n/j/y H:i',
        'long'     => 'D M j Y H:i',
    );
}
