<?php

namespace mindplay\datetime;

use DateTime;
use DateTimeZone;
use RuntimeException;

/**
 * @property int               $time     timestamp
 * @property-read DateTimeZone $timezone default timezone setting
 * @property-read string       $datetime machine-friendly date/time string representation (MySQL etc.)
 * @property-read string       $date     machine-friendly date-only string representation
 * @property-read string       $short    people-friendly short date/time format
 * @property-read string       $long     people-friendly long date/time format
 * @property-read string       $age      people-friendly timestamp age
 */
class DateTimeHelper
{
    /**
     * Initialize DateTimeHelper with default configuration
     */
    public function __construct()
    {
        $this->config = new DateTimeConfig();

        $this->_time = new DateTime('now', $this->config->timezone);
    }

    /**
     * @var DateTimeHelperConfig
     */
    public $config;

    /**
     * @var DateTime
     */
    protected $_time;

    /**
     * @var DateTimeZone default timezone (applies when setting the time)
     */
    protected $_timezone;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $fn = "get_$name";

        return method_exists($this, $fn)
            ? $this->$fn()
            : $this->format($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->{"set_$name"}($value);
    }

    /**
     * @return string
     * @see format()
     */
    public function __toString()
    {
        return $this->format();
    }

    /**
     * @return int integer timestamp
     * @see $time
     */
    protected function get_time()
    {
        return $this->_time->getTimestamp();
    }

    /**
     * @param int|null $time integer timestamp
     *
     * @see $time
     * @throws RuntimeException
     */
    protected function set_time($time)
    {
        if (false === is_int($time)) {
            throw new RuntimeException("invalid value: " . var_export($time, true));
        }

        $this->_time->setTimezone($this->config->timezone);
        $this->_time->setTimestamp($time);
    }

    /**
     * Get the timezone
     *
     * @return DateTimeZone
     * @see $timezone
     */
    protected function get_timezone()
    {
        return $this->_time->getTimezone();
    }

    /**
     * Change the timezone
     *
     * @param DateTimeZone|string $timezone
     *
     * @return self
     */
    public function timezone($timezone)
    {
        $this->_time->setTimezone(is_string($timezone) ? new DateTimeZone($timezone) : $timezone);

        return $this;
    }

    /**
     * Change the timezone to UTC
     *
     * @return self
     */
    public function utc()
    {
        return $this->timezone('UTC');
    }

    /**
     * Reset the time to 00:00:00 (midnight)
     *
     * @return self
     */
    public function date()
    {
        $this->_time->setTime(0, 0, 0);

        return $this;
    }

    /**
     * Reset the date/time to the first day of the month (at 00:00:00)
     *
     * @return self
     */
    public function month()
    {
        $this->_time->setDate($this->_time->format('Y'), $this->_time->format('n'), 1);

        return $this;
    }

    /**
     * @param string $string interval string ('1 day', '6 months', '2 years', '20 minutes', etc.)
     *
     * @see DateInterval::modify()
     * @return self
     */
    public function add($string)
    {
        $this->_time->modify("+$string");

        return $this;
    }

    /**
     * @param string $string interval string ('1 day', '6 months', '2 years', '20 minutes', etc.)
     *
     * @see DateInterval::modify()
     * @return self
     */
    public function sub($string)
    {
        $this->_time->modify("-$string");

        return $this;
    }

    /**
     * @param string $format format name, or format string
     *
     * @return string
     * @see DateTimeHelperConfig::$formats
     * @see DateTime::format()
     */
    public function format($format = 'default')
    {
        if (isset($this->config->formats[$format])) {
            $format = $this->config->formats[$format];
        }

        return $this->_time->format($format);
    }

    /**
     * @see $since
     */
    protected function get_age()
    {
        return $this->since();
    }

    public function since($time = null, $granularity = 2)
    {
        if ($time === null) {
            $time = $this->get_time();
        }

        $delta = time() - $time;

        static $periods = array(
            'year'   => 31536000,
            'month'  => 2628000,
            'week'   => 604800,
            'day'    => 86400,
            'hour'   => 3600,
            'minute' => 60,
            'second' => 1
        );

        if ($delta < 5) {
            return 'now';
        } else {
            $since = '';

            foreach ($periods as $key => $value) {
                if ($delta >= $value) {
                    $time = floor($delta / $value);
                    $delta %= $value;
                    $since .= ($since === '' ? '' : ' ') . $time . ' ';
                    $since .= (($time > 1) ? $key . 's' : $key);

                    if (--$granularity === 0) {
                        return $since;
                    }
                }
            }

            return $since;
        }
    }
}
