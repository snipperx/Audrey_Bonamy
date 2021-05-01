<?php
/**
 * Extends DateTime allowing supplied timezone to be a string, which can also be a UTC offset.
 * Also prevents an exception being thrown. Some additional shortcuts added so less coding is required for regular tasks.
 * By doing this, we support WP's option to manually offset time with a UTC timezone name e.g. UTC+1.5, which isn't supported by PHP (it only supports numerical offsets which represent UTC offsets)
 *
 * @since 5.8.2
 */
class EM_DateTime extends DateTime {
	
	/**
	 * The name of this timezone. For example, America/New_York or UTC+3.5
	 * @var string
	 */
	protected $timezone_name = false;
	/**
	 * @var bool Whether or not string is UTC offset with a UTC+-d timezone name pattern, which isn't supported in PHP normally.
	 */
	protected $timezone_utc = false;
	/**
	 * Flag for validation purposes, so we can still have a real EM_DateTime and extract dates but know if the intended datetime failed validation.
	 * A completely invalid date and time will become 1970-01-01 00:00:00 in local timezone, however a valid time can still exist with the 1970-01-01 date.
	 * If the date is invalid, only local timezones should be used since the time will not accurately convert timezone switches.
	 * @var string
	 */
	public $valid = true;
	
	/**
	 * @see DateTime::__construct()
	 * @param string $time
	 * @param string|EM_DateTimeZone $timezone Unlike DateTime this also accepts string representation of a valid timezone, as well as UTC offsets in form of 'UTC -3' or just '-3'
	 */
	public function __construct( $time = 'now', $timezone = null ){
		//get our EM_DateTimeZone
		$timezone = EM_DateTimeZone::create($timezone);
		//save timezone name for use in getTimezone()
		$this->timezone_name = $timezone->getName();
		$this->timezone_utc = $timezone->utc_offset !== false;
		//fix DateTime error if a regular timestamp is supplied without prepended @ symbol
		if( is_numeric($time) ){
			$time = '@'.$time;
		}elseif( is_null($time) ){
			$time = 'now';
		}
		//finally, run parent function with our custom timezone
		try{
			@parent::__construct( (string) $time, $timezone);
			if( substr($time,0,1) == '@' || $time == 'now' ) $this->setTimezone($timezone);
			$this->valid = true; //if we get this far, supplied time is valid
		}catch( Exception $e ){
			//get current date/time in relevant timezone and set valid flag to false
			try {
				parent::__construct('@0');
			}catch( Exception $e ){
				// do nothing
			}finally{
				$this->setTimezone($timezone);
				$this->setDate(1970,1,1);
				$this->setTime(0,0,0);
				$this->valid = false;
			}
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see DateTime::format()
	 */
	public function format( $format = 'Y-m-d H:i:s'){
		if( !$this->valid && ($format == 'Y-m-d' || $format == em_get_date_format())) return '';
		if( $format !== 'Y-m-d H:i:s' ) $format = $this->formatTimezones($format); // format UTC timezones
		return parent::format($format);
	}
	
	/**
	 * Formats timezone name/abbreviation placeholders when there is a manual offset, which would be passed onto date formatting functions and usually output UTC timezone information.
	 * @param string $format The format to be parsed.
	 * @return string
	 */
	public function formatTimezones($format){
		if( $this->timezone_utc ){
			$timezone_formats = array( 'T', 'e' );
			foreach ( $timezone_formats as $timezone_format ) {
				if ( false !== strpos( $format, $timezone_format ) ) {
					$format = ' '.$format;
					$format = preg_replace( "/([^\\\])$timezone_format/", "\\1" . backslashit( $this->timezone_name ), $format );
					$format = substr( $format, 1, strlen( $format ) -1 );
				}
			}
		}
		return $format;
	}
	
	/**
	 * Returns a date and time representation in the format stored in Events Manager settings.
	 * @param bool $include_hour
	 * @return string
	 */
	public function formatDefault( $include_hour = true ){
		$format = $include_hour ? em_get_date_format() . ' ' . em_get_hour_format() : em_get_date_format();
		$format = apply_filters( 'em_datetime_format_default', $format, $include_hour );
		return $this->i18n( $format );
	}
	
	/**
	 * Provides a translated date and time according to the current blog language.
	 * Useful if using formats that provide date-related names such as 'Monday' or 'January', which should be translated if displayed in another language.
	 * @param string $format
	 * @return string
	 */
	public function i18n( $format = 'Y-m-d H:i:s' ){
		if( !$this->valid && $format == em_get_date_format()) return '';
		// since we use WP's date functions which don't use DateTime (and if so, don't inherit our timezones), we need to preformat timezone related formats, adapted from date_i18n
		$format = $this->formatTimezones( $format );
		// support for < WP 5.3.0
		if( function_exists('wp_date') ){
			return wp_date( $format, $this->getTimestamp(), $this->getTimezone() );
		}else{
			return date_i18n( $format, $this->getTimestampWithOffset(true) );
		}
	}
	
	/**
	 * Outputs a default mysql datetime formatted string.
	 * @return string
	 */
	public function __toString(){
		return $this->format('Y-m-d H:i:s');
	}
	
	/**
	 * Modifies the time of this object, if a mysql TIME valid format is provided (e.g. 14:30:00).
	 * Returns EM_DateTime object in all cases, but $this->valid will be set to false if unsuccessful
	 * @param string $hour
	 * @return EM_DateTime Returns object for chaining.
	 */
	public function setTimeString( $hour ){
		if( preg_match('/^\d{2}:\d{2}:\d{2}$/', $hour) ){
			$time = explode(':', $hour);
			$this->setTime($time[0], $time[1], $time[2]);
		}else{
			$this->valid = false;
		}
		return $this;
	}
	
	/**
	 * Sets timestamp and returns EM_DateTime object in all cases, but $this->valid will be set to false if unsuccessful
	 * @param int $timestamp
	 * @see DateTime::setTimestamp()
	 * @return EM_DateTime
	 */
	public function setTimestamp( $timestamp ){
		$return = parent::setTimestamp( $timestamp );
		$this->valid = $return !== false;
		return $this;
	}
	
	/**
	 * Extends DateTime functionality by accepting a false or string value for a timezone. If set to false, default WP timezone will be used.
	 * Returns EM_DateTime object in all cases, but $this->valid will be set to false if unsuccessful
	 * @param string|false $timezone
	 * @see DateTime::setTimezone()
	 * @return EM_DateTime Returns object for chaining.
	 */
	public function setTimezone( $timezone = false ){
		if( $timezone == $this->getTimezone()->getName() ) return $this;
		$timezone = EM_DateTimeZone::create($timezone);
		$return = parent::setTimezone($timezone);
		$this->timezone_name = $timezone->getName();
		$this->timezone_utc = $timezone->utc_offset !== false;
		$this->valid = $return !== false;
		return $this;
	}
	
	/**
	 * Sets time along and returns EM_DateTime object in all cases, but $this->valid will be set to false if unsuccessful
	 * {@inheritDoc}
	 * @see DateTime::setTime()
	 */
	public function setTime( $hour, $minute, $second = NULL, $microseconds = NULL ){
		$return = parent::setTime( (int) $hour, (int) $minute, (int) $second );
		$this->valid = $return !== false;
		return $this;
	}
	
	/**
	 * Sets date along and returns EM_DateTime object in all cases, but $this->valid will be set to false if unsuccessful
	 * {@inheritDoc}
	 * @see DateTime::setDate()
	 */
	public function setDate( $year, $month, $day ){
		$return = parent::setDate( $year, $month, $day );
		$this->valid = $return !== false;
		return $this;
	}
	
	/**
	 * Returns EM_DateTime object in all cases, but $this->valid will be set to false if unsuccessful
	 * {@inheritDoc}
	 * @see DateTime::setISODate()
	 */
	public function setISODate( $year, $week, $day = NULL ){
		$return = parent::setISODate( $year, $week, $day );
		$this->valid = $return !== false;
		return $this;
	}
	
	/**
	 * Returns EM_DateTime object in all cases, but $this->valid will be set to false if unsuccessful
	 * {@inheritDoc}
	 * @see DateTime::modify()
	 */
	public function modify( $modify ){
		$result = parent::modify($modify);
		$this->valid = $result !== false;
		return $this;
	}
	
	/**
	 * Extends DateTime function to allow string representation of argument passed to create a new DateInterval object.
	 * Returns EM_DateTime object in all cases, but $this->valid will be set to false if unsuccessful
	 * @see DateTime::add()
	 * @param string|DateInterval
	 * @return EM_DateTime Returns object for chaining.
	 * @throws Exception
	 */
	public function add( $DateInterval ){
		if( is_object($DateInterval) ){
			$result = parent::add($DateInterval);
		}else{
			$result = parent::add( new DateInterval($DateInterval) );
		}
		$this->valid = $result !== false;
		return $this;
	}
	
	/**
	 * Extends DateTime function to allow string representation of argument passed to create a new DateInterval object.
	 * Returns EM_DateTime object in all cases, but $this->valid will be set to false if unsuccessful
	 * @see DateTime::sub()
	 * @param string|DateInterval
	 * @return EM_DateTime
	 * @throws Exception
	 */
	public function sub( $DateInterval ){
		if( is_object($DateInterval) ){
			$result = parent::sub($DateInterval);
		}else{
			$result = parent::sub( new DateInterval($DateInterval) );
		}
		$this->valid = $result !== false;
		return $this;
	}
	
	/**
	 * Easy chainable cloning function, useful for situations where you may want to manipulate the current date,
	 * such as adding a month and getting the DATETIME string without changing the original value of this object.
	 * @return EM_DateTime
	 */
	public function copy(){
		return clone $this;
	}
	
	/**
	 * Gets a timestamp with an offset, which will represent the local time equivalent in UTC time.
	 * If using this to supply to a date() function, set $server_localized to true which will account for any rogue code
	 * that sets the server default timezone to something other than UTC (which is WP sets it to at the start)
	 * @param boolean $server_localized
	 * @return int
	 */
	public function getTimestampWithOffset( $server_localized = false ){
		//aside from the actual offset from the timezone, we also have a local server offset we need to deal with here...
		$server_offset = $server_localized ? date('Z',$this->getTimestamp()) : 0;
		return $this->getOffset() + $this->getTimestamp() - $server_offset;
	}
	
	/**
	 * Returns an EM_DateTimeZone object instead of the default DateTimeZone object.
	 * @see DateTime::getTimezone()
	 * @return EM_DateTimeZone
	 */
	public function getTimezone(){
		return new EM_DateTimeZone($this->timezone_name);
	}
	
	/**
	 * Returns a MySQL TIME formatted string, with the option of providing the UTC equivalent.
	 * @param bool $utc If set to true a UTC relative time will be provided.
	 * @return string
	 */
	public function getTime( $utc = false ){
		if( $utc ){
			$current_timezone = $this->getTimezone()->getName();
			$this->setTimezone('UTC');
		}
		$return = $this->format('H:i:s');
		if( $utc ) $this->setTimezone($current_timezone);
		return $return;
	}
	
	/**
	 * Returns a MySQL DATE formatted string.
	 * @param bool $utc
	 * @return string
	 */
	public function getDate( $utc = false ){
		return $this->format('Y-m-d');
	}
	
	/**
	 * Returns a MySQL DATETIME formatted string, with the option of providing the UTC equivalent.
	 * @param bool $utc If set to true a UTC relative time will be provided.
	 * @return string
	 */
	public function getDateTime( $utc = false ){
		if( $utc ){
			$current_timezone = $this->getTimezone()->getName();
			$this->setTimezone('UTC');
		}
		$return = $this->format('Y-m-d H:i:s');
		if( $utc ) $this->setTimezone($current_timezone);
		return $return;
	}
	
	/**
	 * Extends the DateTime::createFromFormat() function by setting the timezone to the default blog timezone if none is provided.
	 * @param string $format
	 * @param string $time
	 * @param string|EM_DateTimeZone $timezone
	 * @return boolean|EM_DateTime
	 */
	public static function createFromFormat( $format, $time, $timezone = null ){
		$timezone = EM_DateTimeZone::create($timezone);
		$DateTime = parent::createFromFormat($format, $time, $timezone);
		if( $DateTime === false ) return false;
		return new EM_DateTime($DateTime->format('Y-m-d H:i:s'), $timezone);
	}
}