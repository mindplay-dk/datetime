<?php

namespace mindplay\datetime;

use DateTime;
use DateTimeZone;
use RuntimeException;

/**
 * @property int               $time     timestamp
 * @property-read DateTimeZone $timezone current timezone
 * @property-read string       $datetime machine-friendly date/time string representation (MySQL DATETIME, etc.)
 * @property-read string       $date     machine-friendly date-only string representation (MySQL DATE, etc.)
 * @property-read string       $short    human-readable short date/time format
 * @property-read string       $long     human-readable long date/time format
 * @property-read string       $age      human-readable timestamp age
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
     * @var DateTimeConfig
     */
    public $config;

    /**
     * @var DateTime
     */
    protected $_time;

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
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $fn = "set_$name";

        if (false === method_exists($this, $fn)) {
            throw new RuntimeException("undefined property \${$name} or accessor {$fn}");
        }

        $this->$fn($value);
    }

    /**
     * @return string
     *
     * @see format()
     *
     * @ignore
     */
    public function __toString()
    {
        return $this->format();
    }

    /**
     * Change the timezone
     *
     * @param DateTimeZone|string|null $timezone timezone name (string) or object (DateTimeZone) or null (reset to default TimeZone)
     *
     * @return $this
     */
    public function timezone($timezone)
    {
        if ($timezone === null) {
            $timezone = $this->config->timezone;
        } else {
            if (is_string($timezone)) {
                $timezone = new DateTimeZone($timezone);
            }
        }

        $this->_time->setTimezone($timezone);

        return $this;
    }

    /**
     * Change the timezone to UTC.
     *
     * @return $this
     */
    public function utc()
    {
        return $this->timezone('UTC');
    }

    /**
     * Reset the time to 00:00:00 (midnight)
     *
     * @return $this
     */
    public function date()
    {
        $this->_time->setTime(0, 0, 0);

        return $this;
    }

    /**
     * Reset the date/time to the first day of the month (at 00:00:00)
     *
     * @return $this
     */
    public function month()
    {
        $this->_time->setDate($this->_time->format('Y'), $this->_time->format('n'), 1);

        return $this;
    }

    /**
     * Add an interval to the current date/time.
     *
     * @param string $string interval string ('1 day', '6 months', '2 years', '20 minutes', etc.)
     *
     * @return $this
     *
     * @see DateInterval::modify()
     */
    public function add($string)
    {
        $this->_time->modify("+$string");

        return $this;
    }

    /**
     * Subtract an interval from the current date/time.
     *
     * @param string $string interval string ('1 day', '6 months', '2 years', '20 minutes', etc.)
     *
     * @return $this
     *
     * @see DateInterval::modify()
     */
    public function sub($string)
    {
        $this->_time->modify("-$string");

        return $this;
    }

    /**
     * Format the current date/time using a named format defined in {@see DateTimeConfig::$formats},
     * or using a format string compatible with {@see DateTime::format()}
     *
     * @param string $format format name, or format string
     *
     * @return string
     *
     * @see DateTimeConfig::$formats
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
     * Parse and set the current date/time, retaining the current timezone.
     *
     * To parse a date/time in a specific timezone, call {@see timezone()} before calling this method.
     *
     * To convert the parsed date/time into a different timezone, call {@see timezone()} after callling this method.
     *
     * If you have an integer timestamp, call {@see datetime()} instead.
     *
     * @param string      $time   a date/time string in the given format
     * @param string|null $format the input format; defaults to NULL meaning "best guess" (a'la {@see strtotime()})
     *
     * @return $this
     */
    public function set($time, $format = null)
    {
        if ($format === null) {
            $this->_time = new DateTime($time, $this->_time->getTimezone());
        } else {
            if (isset($this->config->formats[$format])) {
                $format = $this->config->formats[$format];
            }

            $this->_time = DateTime::createFromFormat($format, $time, $this->_time->getTimezone());
        }

        return $this;
    }

    /**
     * Format as human-readable "age" or "time ago", relative to current system time
     *
     * @param int $granularity
     *
     * @return string
     */
    public function age($granularity = 2)
    {
        $delta = time() - $this->get_time();

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

    /**
     * @return int integer timestamp
     * @see $time
     */
    protected function get_time()
    {
        return $this->_time->getTimestamp();
    }

    /**
     * Set the time (using a UNIX timestamp)
     *
     * @param int|null $time integer UNIX timestamp
     *
     * @see $time
     * @throws RuntimeException
     */
    protected function set_time($time)
    {
        if (false === is_int($time)) {
            throw new RuntimeException("invalid value: " . var_export($time, true));
        }

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
     * @see $age
     */
    protected function get_age()
    {
        return $this->age();
    }
}
