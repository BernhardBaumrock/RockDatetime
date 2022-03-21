# RockDatetime

## A message to Russian 🇷🇺 people

If you currently live in Russia, please read [this message](https://github.com/Roave/SecurityAdvisories/blob/latest/ToRussianPeople.md).

[![SWUbanner](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)

---

This module provides handy methods when working with dates and times in PHP and ProcessWire. Some examples in this readme use TracyDebuggers console and dumping features using shorthand `d()` syntax. If you don't know TracyDebugger it's time to grab a copy and marvel.

## Usage

```php
$format = "%A, %d.%m.%Y %H:%M Uhr";
$time = new RockDatetime("2020-02-25 13:00");
echo $time->format($format); // Dienstag, 25.02.2020 12:55 Uhr
echo $time->firstOfMonth()->format($format); // Samstag, 01.02.2020 00:00 Uhr
```

## Concept

A RockDatetime object holds not only the timestamp but also information for displaying a formatted string of the time and does also have several handy methods that help you when finding the very first second of a month, comparing dates or transforming strings into timestamps.

## Creating a new RockDatetime object

```php
$d = new RockDatetime(); // current timestamp

$d = new RockDatetime("2020-01-01");

$d = $modules->get('RockDatetime');
$d->setTime("2020-01-01");
```

## Defining options

Working with dates and times you'll likely want to format them equally across your website most of the time. That's why RockDatetime takes options defined in `config.php` but leaving you the freedom of overriding them whenever necessary:

```php
// site/config.php
// If you don't define options the defaults will be taken
$config->RockDatetime = [
  'date' => "%d.%m.%Y",
  'time' => "%H:%M",
  'datetime' => "Day {date} @ Time {time}",
];

// use global options
$d = new RockDatetime();
d($d->format()); // Day 25.02.2020 @ Time 20:06

// set options on construct of object
// date formatting from global config will stay intact
$d = new RockDatetime(['time' => '%H*%M*%S - WOHOO!']);
d($d->format()); // Day 25.02.2020 @ Time 20*06*04 - WOHOO!

// set options inline in format() call
// time is taken from config (not from the previous example!)
$d = new RockDatetime();
d($d->format(['date' => '%Y/%m/%d'])); // Day 2020/02/25 @ Time 20:06
```

## API methods

API methods always return the current RockDatetime instance. This means that all API method calls can be chained with further method colls, eg `$date->setTime("2020-02-25 13:00")->format("%A, %d.%m");`.

### move()

Moving by integer value (seconds):

```php
$d = new RockDatetime();
echo $d; // 2020-02-25 15:58:25
echo $d->move(60*60*24); // 2020-02-26 15:58:25
echo $d; // 2020-02-26 15:58:25 --> also moved!
```

Moving by string (php relative date format):

```php
$d = new RockDatetime();
echo $d; // 2020-02-25 15:58:25
echo $d->move("+1 day"); // 2020-02-26 15:58:25
echo $d; // 2020-02-26 15:58:25 --> also moved!
```

### setTime()

Set the timestamp of the current RockDatetime object. See also the `parse()` method.

```php
$d = new RockDatetime();
echo $d; // 2020-02-25 16:22:13
echo $d->setTime("2020-01-01"); // 2020-01-01 00:00:00
echo $d; // 2020-01-01 00:00:00
```

### setOptions()

```php
$d = new RockDatetime();
echo $d->format(); // 25.02.2020 16:31
$d->setOptions(['time' => "%H:%M Uhr"]);
echo $d; // 25.02.2020 16:31 Uhr
```

## Input

RockDatetime has a `parse()` method that parses any string to an integer timestamp.

```php
$d = new RockDatetime();
echo $d->parse(0); // 0
echo $d->parse("2020-02-02 22:22"); // 1580678520
echo $d->parse("2.2.2020 22:22"); // 1580678520
echo $d->parse("FOOBAR"); // WireException: Unable to parse FOOBAR to timestamp
```

The `parse()` method parses the input and returns the integer value but it does not update the timestamp of the Datetime object! If you want to set a new time use `setTime()` instead, which uses `parse()` under the hood.

If you pass a date directly to the class constructor the date will be set automatically according to the parsed value:

```php
$d = new RockDatetime("2020-02-25 14:00");
echo $d; // 2020-02-25 14:00
```

## Output

You have several options for outputting RockDatetime objects:

### Getting the timestamp

```php
$d = new RockDatetime();
echo $d->int; // 1582637350 (current timestamp)
```

### Getting a formatted string

You have several options here:

**Typecasting the object to a string**

This option is great for using RockDatetimes in selectors or internally this is used for SQL queries to have a consistant representation of time values.

```php
$d = new RockDatetime();
echo $d; // 2020-02-25 14:50:29

$from = new RockDatetime("2020-02");
$to = new RockDatetime("2020-03");
$selector = "template=basic-page,created>=$from,created<$to";
echo $pages->find($selector); // 1033|1034|1035
```

**Locale-aware formatting of the object**

```php
$d = new RockDatetime("25.02.2020 14:00");
echo $d->format(); // 25.02.2020 14:00
echo $d->format("%A, %d.%m.%y (%H:%M Uhr)"); // Dienstag, 25.02.20 (14:00 Uhr)
echo $d->format(['time' => "%H|%M|%S"]); // 25.02.2020 14|30|00
```

## Helper functions

### Get first and last seconds of a day/month/year

When working with dates you often need a very specific point in time, like the first day of the month. PHP comes with relative time formats like `strtotime("first day of this month")`, but the result is often not what I need for my development. That's why I built some custom helper methods that make life a little easier: 

```php
// regular PHP relative date handling
$d = new RockDatetime("first day of this month");
echo $d; // 2020-02-01 15:35:43
```

Notice that the timestamp uses the current time and does not return the first second of this month. This might be exactly what you want, but most of the time I need the very first or very last second, so here's my solution:

```php
$d = new RockDatetime();

echo $d->firstOfDay();   // 2020-02-25 00:00:00
echo $d->lastOfDay();    // 2020-02-25 23:59:59

echo $d->firstOfMonth(); // 2020-02-01 00:00:00
echo $d->lastOfMonth();  // 2020-02-29 23:59:59

echo $d->firstOfYear();  // 2020-01-01 00:00:00
echo $d->lastOfYear();   // 2020-12-31 23:59:59
```

These methods can be combined with the `move()` API method. When a parameter is present it will automatically call the `move()` method for you:

```php
$d = new RockDatetime();
echo $d->firstOfMonth()->move("+9 days"); // 2020-02-10 00:00:00
echo $d->firstOfMonth("+9 days");         // 2020-02-10 00:00:00

echo $d->firstOfMonth("+1 year"); // 2021-02-01 00:00:00
echo $d->lastOfMonth("+1 year");  // 2021-02-29 23:59:59
```

### Others

There are several other handy helper functions like `sameDay()`, `sameYear()` etc.; See all available helper functions in RockDatetime.module.php!

## Time comparisons

* equal, ==
* before, <
* after, >

```php
$d1 = new RockDatetime("2020-01-01");
$d2 = new RockDatetime("2010-01-01");

d($d1->equals("2020-01-01")); // true
d($d1->equals("2020-01-01 12:00")); // false
d($d1 == $d2); // false

d($d1->before("2020-02-02")); // true
d($d1->before($d2)); // false
d($d1 < $d2); // false

d($d1->after("2020-02-02")); // false
d($d1->after($d2)); // true
d($d1 > $d2); // true
```

## Checking for date ranges

```php
$d = new RockDatetime("2020-06-01");
$from = $d->firstOfMonth();
$to = $d->lastOfMonth();
d($d->between($from, $to)); // false, comparing from < current < to
d($d->within($from, $to)); // true, comparing from <= current <= to
```

```php
$d = new RockDatetime("2020-06-01");

$day = new RockDatetime("2020-06-20");
d($d->onDay($day)); // false
d($d->inMonth($day)); // true
d($d->inYear($day)); // true

d($d->onDay("2020-06-02")); // false
d($d->inMonth("2020-06")); // true
d($d->inYear("2020")); // true
```

The `inYear()` method is somewhat special as it transforms 4-letter input into a string like `"$year-01-01"` to make sure that PHP's `strtotime()` recognizes it correctly. See https://bit.ly/37XP7Wo for the issue.
