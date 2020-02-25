<?php namespace ProcessWire;
/**
 * ProcessWire helper module for date and time related stuff
 *
 * @license MIT
 * @author Bernhard Baumrock, 07.02.2020
 * @link https://www.baumrock.com
 */
class RockDatetime extends WireData implements Module {

  /** @var int $int the timestamp of the Datetime object */
  public $int;

  /** @var array $options */
  protected $options;

  public static function getModuleInfo() {
    return [
      'title' => 'RockDatetime',
      'version' => '0.0.1',
      'summary' => 'ProcessWire helper module for date and time related stuff.',
      'autoload' => true,
      'singular' => false,
      'icon' => 'clock-o',
    ];
  }

  public function init() {
  }

  /**
   * Class constructor
   */
  public function __construct($data = null, $options = []) {
    $this->setTime($data ?? time());
    $this->setOptions($options);
  }

  /* #################### API #################### */
  /**
   * API methods always return the current RockDatetime instance. This means
   * that all API method calls can be chained with further method colls, eg
   * $date->setTime("2020-02-25 13:00")->format("%A, %d.%m");
   */

  /**
   * Move current instance by given span
   * @param int|string $span
   * @return RockDatetime
   */
  public function move($span = null) {
    if(!$span) return $this;
    if(is_int($span)) $this->int += $span;
    elseif(is_string($span)) $this->int = strtotime($span, $this->int);
    else throw new WireException("Invalid input for move()");
    return $this;
  }
  
  /**
   * Set timestamp of this instance to given data
   * @param string|int $data
   * @return RockDatetime
   */
  public function setTime($data) {
    $this->int = $this->parse($data);
    return $this;
  }

  /**
   * Set options for this datetime instance
   * @return RockDatetime
   */
  public function setOptions($options = []) {
    $opt = $this->getOptions($options);
    $this->options = (object)array_merge((array)$opt, $options);
    return $this;
  }

  /* #################### END API #################### */

  /* #################### HELPERS #################### */
  /**
   * Helpers do all kinds of stuff but do NOT return or modify the current
   * instance of RockDatetime. Instead they return strings, integers or new
   * RockDatetime instances.
   */
  
  /**
   * Create a copy of current RockDatetime instance
   * @param string|array $data
   * @return RockDatetime
   */
  public function copy($data = null) {
    $copy = new RockDatetime($this->int);
    if(is_string($data)) $copy->move($data);
    if(is_array($data)) $copy->setOptions($data);
    return $copy;
  }

  /**
   * Return first second of current Day
   * This method returns a new RockDatetime object
   * @return RockDatetime
   */
  public function firstOfDay($move = null) {
    $new = new RockDatetime(strtotime(date("Y-m-d", $this->int)));
    return $new->move($move);
  }

  /**
   * Return first second of current Month
   * This method returns a new RockDatetime object
   * @return RockDatetime
   */
  public function firstOfMonth($move = null) {
    $new = new RockDatetime(strtotime(date("Y-m-01", $this->int)));
    return $new->move($move);
  }

  /**
   * Return first second of current Year
   * This method returns a new RockDatetime object
   * @return RockDatetime
   */
  public function firstOfYear($move = null) {
    $new = new RockDatetime(strtotime(date("Y-01-01", $this->int)));
    return $new->move($move);
  }

  /**
   * Return a formatted date string
   * @param string|array $format
   * @return string
   */
  public function format($format = null) {
    // if format was provided as string we return it
    if(is_string($format)) return strftime($format, $this->int);
    
    // otherwise we get the datetime formatting string from options
    $options = $this->getOptions($format ?? []);
    $rep = [
      '{date}' => $options->date,
      '{time}' => $options->time,
    ];
    $format = str_replace(
      array_keys($rep),
      array_values($rep),
      $options->datetime);
    return strftime($format, $this->int);
  }

  /**
   * Return merged options (defaults, config, custom)
   * @return object
   */
  public function getOptions($options = []) {
    if(!is_array($options)) throw new WireException("Parameter of getOptions must be an array");
    
    // if options have not been set yet we set them now
    if(!$this->options) {
      $defaults = [
        'date' => "%d.%m.%Y",
        'time' => "%H:%M",
        'datetime' => "{date} {time}",
      ];
      $config = $this->config->RockDatetime ?: [];
      $this->options = (object)array_merge($defaults, $config, $options);
    }

    // return merged options
    return (object)array_merge((array)$this->options, $options);
  }

  /**
   * Is data a valid timestamp?
   * @return bool
   */
  public function isTimestamp($data) {
    return ( is_numeric($data) && (int)$data == $data );
  }

  /**
   * Return last second of current Day
   * This method returns a new RockDatetime object
   * @return RockDatetime
   */
  public function lastOfDay($move = null) {
    $new = $this->firstOfDay()->move('+1 Day')->move(-1);
    return $new->move($move);
  }

  /**
   * Return last second of current Month
   * This method returns a new RockDatetime object
   * @return RockDatetime
   */
  public function lastOfMonth($move = null) {
    $new = $this->firstOfMonth()->move('+1 month')->move(-1);
    return $new->move($move);
  }

  /**
   * Return last second of current Year
   * This method returns a new RockDatetime object
   * @return RockDatetime
   */
  public function lastOfYear($move = null) {
    $new = $this->firstOfYear()->move('+1 Year')->move(-1);
    return $new->move($move);
  }

  /**
   * Parse given data to an integer timestamp
   * 
   * Use caution with 4-digit strings or integers as they are interpreted as
   * hour and minute of current time:
   * new RockDatetime("2020") --> 2020-02-25 20:20:00
   * 
   * @param string|int|RockDatetime $data
   * @param int $time
   * @return int|false
   */
  public function parse($data, $time = null) {
    if(is_numeric($data)) return (int)$data;

    // we typecast $data to a string so a RockDaterange object can be parsed
    $data = (string)$data;

    // parse the input
    $stamp = $time ? strtotime($data, $time) : strtotime($data);
    if(!$stamp) throw new WireException("Unable to parse $data to timestamp");
    return $stamp;
  }

  /**
   * Return a formatted date using PHP's date() function
   * @return string
   */
  public function phpDate($format) {
    return date($format, $this->int);
  }

  /* #################### END HELPERS #################### */

  /* #################### COMPARISONS #################### */

  /**
     * Is the current instance after a given datetime?
     * @return bool
     */
    public function after($date) {
      return $this > new RockDatetime($date);
    }

    /**
     * Is the current instance before a given datetime?
     * @return bool
     */
    public function before($date) {
      return $this < new RockDatetime($date);
    }

    /**
     * Is the current instance equal to given datetime?
     * @return bool
     */
    public function equal($date) {
      return $this == new RockDatetime($date);
    }

  /* #################### END COMPARISONS #################### */

  /* #################### DATERANGE CHECKS #################### */

    /**
     * Is the current instance between two dates?
     * Similar to within() but returns from < current < to
     * @return bool
     */
    public function between($from, $to) {
      $from = new RockDatetime($from);
      $to = new RockDatetime($to);
      return $from < $this AND $this < $to;
    }

    /**
     * Check if current instance is in given month
     * @return bool
     */
    public function inMonth($month) {
      $m = new RockDatetime($month);
      return $this->within($m->firstOfMonth(), $m->lastOfMonth());
    }

    /**
     * Check if current instance is in given year
     * @return bool
     */
    public function inYear($year) {
      // if a 4-letter string was provided we convert it to a timestamp
      // see https://bit.ly/37XP7Wo
      $year = (string)$year;
      if(strlen($year) === 4) $year = "$year-01-01";
      $y = new RockDatetime($year);
      return $this->within($y->firstOfYear(), $y->lastOfYear());
    }

    /**
     * Check if instance is on given day
     * @return bool
     */
    public function onDay($day) {
      $d = new RockDatetime($day);
      return $this->within($d->firstOfDay(), $d->lastOfDay());
    }

    /**
     * Inclusive date range check.
     * Similar to between but compares from <= current <= to
     * @return bool
     */
    public function within($from, $to) {
      $from = new RockDatetime($from);
      $to = new RockDatetime($to);
      return $from <= $this AND $this <= $to;
    }

  /* #################### END DATERANGE CHECKS #################### */

  /**
   * Return string presentation of this object
   * @return string
   */
  public function __toString() {
    return date("Y-m-d H:i:s", $this->int);
  }

  /**
   * Debug info array
   */
  public function __debugInfo() {
    return [
      // sorted alphabetically (magics first)
      '(string)' => (string)$this,
      'format()' => $this->format(),
      'int' => $this->int,
      'options' => $this->options,
    ];
  }
}
