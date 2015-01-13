<?php

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/src/datetime.func.php';

if (coverage()) {
    coverage()->filter()->addDirectoryToWhitelist(__DIR__ . '/src');

    coverage('test');
}

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
    'Can parse date and time',
    function () {
        $SHORT = '1/29/14 16:58';

        eq(datetime()->set($SHORT, 'short')->time, datetime($SHORT)->time,
            'can parse date/time in a named format');

        $LONG = '2014-01-29 16:58:00';

        eq(datetime()->set($LONG, 'Y-m-d H:i:s')->time, datetime($LONG)->time,
            'can parse date/time in a specified format');
    }
);

test(
    'Can manipulate date and time',
    function () {
        $DATE = '2014-01-29 16:58:00';

        eq(datetime($DATE)->date()->long, 'Wed Jan 29 2014 00:00',
            'date() resets the time to midnight');

        eq(datetime($DATE)->month()->short, '1/1/14 16:58',
            'month() resets the date to the first day of the month');

        eq(datetime($DATE)->year()->short, '1/1/14 16:58',
            'month() resets the date to the first day of the month');

        eq(datetime($DATE)->sunday()->short, '1/26/14 16:58',
            'sunday() resets the date to the first day of the week start on Sunday');

        eq(datetime($DATE)->monday()->short, '1/27/14 16:58',
            'monday() resets the date to the first day of the week start on Monday');

        eq(datetime($DATE)->add('20 minutes')->short, '1/29/14 17:18',
            'add() accepts a legible interval ("20 minutes")');

        eq(datetime($DATE)->sub('20 minutes')->short, '1/29/14 16:38',
            'sub() accepts a legible interval ("20 minutes")');
    }
);

test(
    'Can format date and time',
    function () {
        $DATE = '2014-01-29 16:58:00';

        eq((string) datetime($DATE), '1/29/14 16:58',
            'objct converts itself to a string');

        eq(datetime($DATE)->date, '2014-01-29',
            '$date returns a machine-friendly date');

        eq(datetime($DATE)->datetime, '2014-01-29 16:58:00',
            '$datetime returns a machine-friendly date/time');

        eq(datetime($DATE)->short, '1/29/14 16:58',
            '$short returns a user-friendly short date/time');

        eq(datetime($DATE)->long, 'Wed Jan 29 2014 16:58',
            '$long returns a user-friendly long date/time');

        eq(datetime($DATE)->time, 1391014680,
            '$time returns an integer timestamp');

        eq(datetime($DATE)->format('time'), '16:58:00',
            'format() accepts a named format ("time")');

        eq(datetime($DATE)->format('m.d.Y'), '01.29.2014',
            'format() accepts a custom format ("m.d.Y")');

        eq(datetime()->sub('1 year')->age, '1 year',
            '$age returns the formatted age');

        eq(datetime()->sub('2 months')->age(1), '2 months',
            'age() returns the formatted age [1]');

        eq(datetime()->age(), 'now',
            'age() returns the formatted age [2]');
    }
);

test(
    'Expected Exceptions',
    function () {
        expect(
            'RuntimeException',
            'invalid argument to datetime()',
            function () {
                $foo = (object) array();

                datetime($foo);
            }
        );

        expect(
            'RuntimeException',
            'undefined property write',
            function () {
                datetime()->foo = 'bar';
            }
        );

        expect(
            'RuntimeException',
            'setting invalid time',
            function () {
                datetime()->time = 'no_sir';
            }
        );
    }
);

if (coverage()) {
    // output code coverage report to console:

    $report = new PHP_CodeCoverage_Report_Text(10, 90, false, false);

    echo $report->process(coverage(), false);

    // output code coverage report for integration with CI tools:

    $report = new PHP_CodeCoverage_Report_Clover();

    $report->process(coverage(), 'build/logs/clover.xml');
}

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
 * @param string|null $text description (to start coverage); or null (to stop coverage)
 * @return PHP_CodeCoverage|null
 */
function coverage($text = null)
{
    static $coverage = null;
    static $running = false;

    if ($coverage === false) {
        return null; // code coverage unavailable
    }

    if ($coverage === null) {
        try {
            $coverage = new PHP_CodeCoverage;
        } catch (PHP_CodeCoverage_Exception $e) {
            echo "# Notice: no code coverage run-time available\n";
            $coverage = false;
            return null;
        }
    }

    if (is_string($text)) {
        $coverage->start($text);
        $running = true;
    } else {
        if ($running) {
            $coverage->stop();
            $running = false;
        }
    }

    return $coverage;
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
