# RockDatetime

This module provides handy methods when working with dates and times in PHP and ProcessWire. Examples in this readme use TracyDebuggers console and dumping features using shorthand `d()` syntax. If you don't know TracyDebugger it's time to grab a copy and marvel.

## Usage

```php
$format = "%A, %d.%m.%Y %H:%M Uhr";
$time = new RockDatetime("2020-02-25 13:00");
echo $time->format($format);
// Dienstag, 25.02.2020 12:55 Uhr

echo $time->firstOfMonth()->format($format);
// Samstag, 01.02.2020 00:00 Uhr
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

The `parse()` method parses the input and returns the integer value but it does not update the timestamp of the Datetime object! This is done via `setTime()`, which uses `parse()` under the hood:

```php

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
echo $pages->find("template=basic-page,created>=$from,created<$to");
// 1033|1034|1035
```

**Locale-aware formatting of the object**

```php
$d = new RockDatetime();
echo $d->format(); // 25.02.2020 14:31
echo $d->format("%A, %d.%m.%y (%H:%M Uhr)"); // Dienstag, 25.02.20 (14:33 Uhr)
echo $d->format(['time' => "%H|%M|%S"]); // 25.02.2020 14|40|45
```

## Defining global options

```php
// site/config.php
$config->RockDatetime = [
  'date' => "%d.%m.%Y",
  'time' => "%H:%M",
  'datetime' => "{date} {time}",
];

// example
d(new RockDatetime(), 'default');
$config->RockDatetime = ['date' => "%Y--%m--%d"];
$d = new RockDatetime("2020-02-22 12:30", ['time' => "%H:%M Uhr"]);
d($d, 'custom');
```

![img](https://i.imgur.com/1DsHkRA.png)
