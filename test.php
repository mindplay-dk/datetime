<?php

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require __DIR__ . '/vendor/autoload.php';
$autoloader->addPsr4('mindplay\datetime\\', __DIR__ . '/src');

require __DIR__ . '/datetime.php';

test(
    'Can handles timezones',
    function() {
        eq('Defaults to UTC', datetime()->timezone->getName(), 'UTC');

        $UTC = '2014-01-29 16:58:00'; // sample UTC time
        $EST = '2014-01-29 11:58:00';  // same in NYC (UTC-5)

        $time = datetime($UTC)->time;

        eq('Converts from UTC to timestamp and back', datetime($UTC)->time, datetime($time)->time);

        eq('Converts to UTC string and back', datetime($time)->datetime, datetime($UTC)->datetime);

        eq('Can set timezone', datetime($UTC)->timezone('America/New_York')->timezone->getName(), 'America/New_York');

        eq('Changing the timezone does not affect the timestamp', datetime($UTC)->time, datetime($UTC)->timezone('America/New_York')->time);

        eq('Formats time using EST timezone [1]', datetime($UTC)->timezone('America/New_York')->datetime, $EST);
        eq('Formats time using EST timezone [2]', datetime()->set($UTC)->timezone('America/New_York')->datetime, $EST);
        eq('Formats time using EST timezone [3]', datetime($UTC, 'UTC')->timezone('America/New_York')->datetime, $EST);

        eq('Correctly initializes with timezone [1]', datetime($EST, 'America/New_York')->datetime, $EST);
        eq('Correctly initializes with timezone [2]', datetime($UTC)->datetime, $UTC);

        eq('Converts from EST string back to UTC string', datetime($EST, 'America/New_York')->utc()->datetime, datetime($UTC)->datetime);
    }
);

test(
    'Adds and subtracts intervals',
    function () {
        eq('Adds 2 minutes', datetime('2014-01-29 16:58:00')->add('2 minutes')->datetime, '2014-01-29 17:00:00');
        eq('Subtracts 2 minutes', datetime('2014-01-29 16:58:00')->sub('2 minutes')->datetime, '2014-01-29 16:56:00');
    }
);

test(
    'Formats dates and times',
    function () {
        $DATE = '2014-01-29 16:58:00';

        eq('$date returns a machine-friendly date', datetime($DATE)->date, '2014-01-29');
        eq('$datetime returns a machine-friendly date/time', datetime($DATE)->datetime, '2014-01-29 16:58:00');
        eq('$short returns a user-friendly short date/time', datetime($DATE)->short, '1/29/14 16:58');
        eq('$long returns a user-friendly long date/time', datetime($DATE)->long, 'Wed Jan 29 2014 16:58');
        eq('date() resets the time to midnight', datetime($DATE)->date()->long, 'Wed Jan 29 2014 00:00');
        eq('timezone() converts to EST', datetime($DATE)->timezone('EST')->short, '1/29/14 11:58');
        eq('timezone() converts to PST', datetime($DATE)->timezone('PST')->short, '1/29/14 08:58');
        eq('$time returns an integer timestamp', datetime($DATE)->time, 1391014680);
        eq('format() accepts a named format ("time")', datetime($DATE)->format('time'), '16:58:00');
        eq('format() accepts a custom format ("m.d.Y")', datetime($DATE)->format('m.d.Y'), '01.29.2014');
        eq('month() resets the date to the first day of the month', datetime($DATE)->month()->short, '1/1/14 16:58');
        eq('add() accepts a legible interval ("20 minutes")', datetime($DATE)->add('20 minutes')->short, '1/29/14 17:18');
        eq('sub() accepts a legible interval ("20 minutes")', datetime($DATE)->sub('20 minutes')->short, '1/29/14 16:38');
    }
);

// https://gist.github.com/mindplay-dk/4260582

/**
 * @param string   $name     test description
 * @param callable $function test implementation
 */
function test($name, Closure $function)
{
    echo "\n=== $name ===\n\n";

    try {
        $function();
    } catch (Exception $e) {
        echo "\n*** TEST FAILED ***\n\n$e\n";
    }
}

/**
 * @param string $text   description of assertion
 * @param bool   $result result of assertion
 * @param mixed  $value  optional value (displays on failure)
 */
function ok($text, $result, $value = null)
{
    if ($result === true) {
        echo "- PASS: $text\n";
    } else {
        echo "# FAIL: $text" . ($value === null ? '' : ' (' . (is_string($value) ? $value : var_export($value, true)) . ')') . "\n";
    }
}

/**
 * @param string $text   description of assertion
 * @param mixed  $value  value
 * @param mixed  $value  expected value
 */
function eq($text, $value, $expected) {
    ok($text, $value === $expected, "expected: " . var_export($expected, true) . ", got: " . var_export($value, true));
}

/**
 * @param string   $text           description of assertion
 * @param string   $exception_type Exception type name
 * @param callable $function       function expected to throw
 */
function expect($text, $exception_type, Closure $function)
{
    try {
        $function();
    } catch (Exception $e) {
        if ($e instanceof $exception_type) {
            ok("$text (expected $exception_type)", true);
            return;
        } else {
            $actual_type = get_class($e);
            ok("$text (expected $exception_type but $actual_type was thrown)", false);
            return;
        }
    }

    ok("$text (expected $exception_type but no exception was thrown)", false);
}
