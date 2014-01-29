<?php

require __DIR__ . '/datetime.php';
require __DIR__ . '/mindplay/datetime/DateTimeHelper.php';
require __DIR__ . '/mindplay/datetime/DateTimeConfig.php';

test(
    'Handles timezones',
    function() {
        eq('Defaults to UTC', datetime()->timezone->getName(), 'UTC');

        $UTC = '2014-01-29 16:58:00'; // sample UTC time
        $EST = '2014-01-29 11:58:00';  // same in NYC (UTC-5)

        $time = datetime($UTC)->time;

        eq('Converts from UTC to timestamp and back', datetime($UTC)->time, datetime($time)->time);

        eq('Converts to UTC string and back', datetime($time)->datetime, datetime($UTC)->datetime);

        eq('Can set timezone', datetime($UTC)->timezone('America/New_York')->timezone->getName(), 'America/New_York');

        eq('Changing the timezone does not affect the timestamp', datetime($UTC)->time, datetime($UTC)->timezone('America/New_York')->time);

        eq('Formats time using EST timezone', datetime($UTC)->timezone('America/New_York')->datetime, $EST);

        eq('Correctly initializes with timezone', datetime($EST, 'America/New_York')->datetime, $EST);

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
