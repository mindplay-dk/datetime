mindplay/datetime
-----------------

[![Build Status](https://travis-ci.org/mindplay-dk/datetime.svg?branch=master)](https://travis-ci.org/mindplay-dk/datetime)

[![Code Coverage](https://scrutinizer-ci.com/g/mindplay-dk/datetime/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mindplay-dk/datetime/?branch=master)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mindplay-dk/datetime/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mindplay-dk/datetime/?branch=master)

This date/time helper tries to make date/time management in PHP less prickly, by
providing a chainable helper that can be accessed via a global function `datetime()`.

I wanted a convenient way to work with timestamps, which would provide the same
convenience (and IDE support) as e.g. `DateTime`, but without the problems.

The global `datetime()` function takes an integer timestamp as argument, or a
valid date/time string compatible with `strtotime()`, applies it to the helper,
and returns it.

    $time = datetime('1975-07-07')->time; // parse date/time string to timestamp

    $str = datetime(time())->datetime; // timestamp to 'YYYY-MM-DD HH:MM:SS' format

The helper has a configuration object that defines global date/time formats and
a default timezone, which is UTC by default, regardless of any environment
settings - since timestamps (unlike `DateTime`) do not carry around timezone
information, the default timezone can be changed, but you can also switch to
different timezones for easy string output.

    $a = datetime()->utc()->long; // system date/time in long format

    $b = datetime()->timezone('EST')->short; // short date/time in EST timezone

The helper implements `__toString()` and will format itself using the `'default'`
format, which is also configurable. You can define as many formats as you want,
or specify the format directly, and you can render these using `->format()` at
the end of a chain. Default formats like `'short'`, `'long'`, `'string'` and
`'date'` are also supported directly as (dynamic) properties.

    datetime()->config->formats['weekday'] = 'l';

    echo datetime()->timezone('PST')->format('weekday'); // current weekday in PST

Basic computations can also be performed using plain english:

    $today = datetime()->date()->time; // date() resets the time to 00:00:00

    $this_month = datetime()->month()->time; // month() resets to start of the month

    $next_week = datetime()->add('1 week')->time;

    $whenever = datetime()->add('1 month')->sub('3 days 2 hours 1 minute')->time;

One word of caution: unless you `echo` the result (invoking `__toString()`) your
call-chains should always end with a property rather than a method `()` call - the
properties of the helper-class never return the helper object, always a value.

    $bad = datetime(); // reference to DateTimeHelper !

    $wrong = datetime()->timezone('EST')->add('1 week'); // oh noes!

There's a bunch of other features, go ahead and check out the unit-test for examples
of every possible operation, or play around using auto-complete in your IDE.


Why not objects?
----------------

Nobody doesn't love objects, but `DateTime` is trouble - watch:

    $today = new DateTime();
    $yesterday = $today->modify('-1 day');
    
    echo "today: " . $today->format('Y-m-d') . "\n";
    echo "yesterday: " . $yesterday->format('Y-m-d');

If you understand how `DateTime` works, you might be prepared for this nonsense:

    today: 2013-03-21
    yesterday: 2013-03-21

Methods like `modify()`, `add()` and `sub()` modify the `DateTime` object, rather
than returning a new `DateTime` instance.

And sure, you got `DateTimeImmutable` in more recent versions of PHP (and before
it, dozens of third-party immutable date/time object implementations in userland)
but you're still working with objects.

A timestamp is a value - when dealing with timestamps, I therefore want a value
type, not an object, which is more complicated to handle when dealing with e.g.
serialization, object/relational-mapping, etc.

When dealing with timezones, I don't want the timezone attached to the timestamp,
because the timestamp and timezone do not actually have a meaningful relationship:
timestamps are absolute, and that doesn't change when you attach it to a timezone;
the only time that isn't true, is when you want the date/time as a string, but
it's often more confusing to carry around the timezone information with the value
for later use, since, when it finally gets turned into a string, it may not be
obvious what timezone is being used - this can make programs rather confusing
and difficult to read.
