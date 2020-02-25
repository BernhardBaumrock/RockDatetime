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
   * @return RockDatetime
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
   * Parse given data to an integer timestamp
   * @param string|int $data
   * @return int|false
   */
  public function parse($data) {
    // if data is an integer we return it as timestamp
    if(is_numeric($data)) return (int)$data;

    // if it is a string we try strtotime
    if(is_string($data)) {
      $time = strtotime($data);
      if($time) return $time;
    }

    throw new WireException("Unable to parse $data to timestamp");
  }

  /**
   * Return a formatted date using PHP's date() function
   * @return string
   */
  public function phpDate($format) {
    return date($format, $this->int);
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


  /* #################### END HELPERS #################### */

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
