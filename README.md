This is a date/time helper for convenient work with integer timestamps.

As working with timestamps (integers) can be rather clumsy, I've been trying to work
with `DateTime` instead, and found that to be even worse, and rather error-prone.

Here's a simple example that demonstrates the problem:

    $today = new DateTime();
    $yesterday = $today->modify('-1 day');
    
    echo "today: " . $today->format('Y-m-d') . "\n";
    echo "yesterday: " . $yesterday->format('Y-m-d');

If you understand how `DateTime` works, you might expect the following nonsense:

    today: 2013-03-21
    yesterday: 2013-03-21

Methods like `modify()`, `add()` and `sub()` modify the `DateTime` object, rather
than returning a new `DateTime` instance. Bunk.

Of course, you can work around this by manually cloning your DateTime objects, but
this can get rather ugly, and seems counter-intuitive - most people think of a
simple date/time value as being a _value_ rather than an object, and as such you
would expect it would always be copied rather than referenced; the same as any
other value, say, a string or integer.

Countless libraries have attempted to work around this by extending `DateTime` and
fixing these issues by having most methods automatically `clone` and return a copy
rather than modifying the original object. This generally leads to problems with
serialization and object/relational-mapping, where you now need to explicitly
support a new extended `DateTime` type.

The bottom line is that I don't want what is essentially a value-type encapsulated
as an object - it's just dumb.

Back to timestamps then: I wanted a convenient way to work with timestamps, that
would provide the same convenience (and IDE support) as with `DateTime`, without
the ouchy cactus-like feel.

My solution is a simple global function that provides access to a helper-class
with chainable methods.

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
chains should always end with a property rather than a method `()` call - by
convention, properties never return the helper object.

    $bad = datetime(); // reference to DateTimeHelper !
    
    $wrong = datetime()->timezone('EST')->add('1 week'); // oh noes!

That's all for now...
