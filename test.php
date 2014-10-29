<?php

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require __DIR__ . '/vendor/autoload.php';
$autoloader->addPsr4('mindplay\datetime\\', __DIR__ . '/src');

require __DIR__ . '/datetime.php';

test(
    'Can handles timezones',
    function () {
        eq(datetime()->timezone->getName(), 'UTC', 'Defaults to UTC');

        $UTC = '2014-01-29 16:58:00'; // sample UTC time
        $EST = '2014-01-29 11:58:00';  // same in NYC (UTC-5)

        $time = datetime($UTC)->time;

        eq(datetime($UTC)->time, datetime($time)->time,
            'Converts from UTC to timestamp and back');

        eq(datetime($time)->datetime, datetime($UTC)->datetime,
            'Converts to UTC string and back');

        eq(datetime($UTC)->timezone('America/New_York')->timezone->getName(), 'America/New_York',
            'Can set timezone');

        eq(datetime($UTC)->time, datetime($UTC)->timezone('America/New_York')->time,
            'Changing the timezone does not affect the timestamp');

        eq(datetime($UTC)->timezone('America/New_York')->datetime, $EST,
            'Formats time using EST timezone [1]');

        eq(datetime()->set($UTC)->timezone('America/New_York')->datetime, $EST,
            'Formats time using EST timezone [2]');

        eq(datetime($UTC, 'UTC')->timezone('America/New_York')->datetime, $EST,
            'Formats time using EST timezone [3]');

        eq(datetime($EST, 'America/New_York')->datetime, $EST,
            'Correctly initializes with timezone [1]');

        eq(datetime($UTC)->datetime, $UTC,
            'Correctly initializes with timezone [2]');

        eq(datetime($EST, 'America/New_York')->utc()->datetime, datetime($UTC)->datetime,
            'Converts from EST string back to UTC string');
    }
);

test(
    'Adds and subtracts intervals',
    function () {
        eq(datetime('2014-01-29 16:58:00')->add('2 minutes')->datetime, '2014-01-29 17:00:00',
            'Adds 2 minutes');

        eq(datetime('2014-01-29 16:58:00')->sub('2 minutes')->datetime, '2014-01-29 16:56:00',
            'Subtracts 2 minutes');
    }
);

test(
    'Formats dates and times',
    function () {
        $DATE = '2014-01-29 16:58:00';

        eq(datetime($DATE)->date, '2014-01-29',
            '$date returns a machine-friendly date');

        eq(datetime($DATE)->datetime, '2014-01-29 16:58:00',
            '$datetime returns a machine-friendly date/time');

        eq(datetime($DATE)->short, '1/29/14 16:58',
            '$short returns a user-friendly short date/time');

        eq(datetime($DATE)->long, 'Wed Jan 29 2014 16:58',
            '$long returns a user-friendly long date/time');

        eq(datetime($DATE)->date()->long, 'Wed Jan 29 2014 00:00',
            'date() resets the time to midnight');

        eq(datetime($DATE)->timezone('EST')->short, '1/29/14 11:58',
            'timezone() converts to EST');

        eq(datetime($DATE)->timezone('PST')->short, '1/29/14 08:58',
            'timezone() converts to PST');

        eq(datetime($DATE)->time, 1391014680,
            '$time returns an integer timestamp');

        eq(datetime($DATE)->format('time'), '16:58:00',
            'format() accepts a named format ("time")');

        eq(datetime($DATE)->format('m.d.Y'), '01.29.2014',
            'format() accepts a custom format ("m.d.Y")');

        eq(datetime($DATE)->month()->short, '1/1/14 16:58',
            'month() resets the date to the first day of the month');

        eq(datetime($DATE)->add('20 minutes')->short, '1/29/14 17:18',
            'add() accepts a legible interval ("20 minutes")');

        eq(datetime($DATE)->sub('20 minutes')->short, '1/29/14 16:38',
            'sub() accepts a legible interval ("20 minutes")');
    }
);

exit(status());

// https://gist.github.com/mindplay-dk/4260582

/**
 * @param string   $name     test description
 * @param callable $function test implementation
 */
function test($name, $function)
{
    echo "\n=== $name ===\n\n";

    try {
        call_user_func($function);
    } catch (Exception $e) {
        ok(false, "UNEXPECTED EXCEPTION", $e);
    }
}

/**
 * @param bool   $result result of assertion
 * @param string $why    description of assertion
 * @param mixed  $value  optional value (displays on failure)
 */
function ok($result, $why = null, $value = null)
{
    if ($result === true) {
        echo "- PASS: " . ($why === null ? 'OK' : $why) . ($value === null ? '' : ' (' . format($value) . ')') . "\n";
    } else {
        echo "# FAIL: " . ($why === null ? 'ERROR' : $why) . ($value === null ? '' : ' - ' . format($value, true)) . "\n";
        status(false);
    }
}

/**
 * @param mixed  $value    value
 * @param mixed  $expected expected value
 * @param string $why      description of assertion
 */
function eq($value, $expected, $why = null)
{
    $result = $value === $expected;

    $info = $result
        ? format($value)
        : "expected: " . format($expected, true) . ", got: " . format($value, true);

    ok($result, ($why === null ? $info : "$why ($info)"));
}

/**
 * @param string   $exception_type Exception type name
 * @param string   $why            description of assertion
 * @param callable $function       function expected to throw
 */
function expect($exception_type, $why, $function)
{
    try {
        call_user_func($function);
    } catch (Exception $e) {
        if ($e instanceof $exception_type) {
            ok(true, $why, $e);
            return;
        } else {
            $actual_type = get_class($e);
            ok(false, "$why (expected $exception_type but $actual_type was thrown)");
            return;
        }
    }

    ok(false, "$why (expected exception $exception_type was NOT thrown)");
}

/**
 * @param mixed $value
 * @param bool  $verbose
 *
 * @return string
 */
function format($value, $verbose = false)
{
    if ($value instanceof Exception) {
        return get_class($value)
        . ($verbose ? ": \"" . $value->getMessage() . "\"" : '');
    }

    if (! $verbose && is_array($value)) {
        return 'array[' . count($value) . ']';
    }

    if (is_bool($value)) {
        return $value ? 'TRUE' : 'FALSE';
    }

    if (is_object($value) && !$verbose) {
        return get_class($value);
    }

    return print_r($value, true);
}

/**
 * @param bool|null $status test status
 *
 * @return int number of failures
 */
function status($status = null)
{
    static $failures = 0;

    if ($status === false) {
        $failures += 1;
    }

    return $failures;
}
