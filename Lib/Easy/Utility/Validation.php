<?php

/**
 * Offers different validation methods.
 *
 */
class Validation {
	
	/**
	 * Some complex patterns needed in multiple places
	 *
	 * @var array
	 */
	protected static $_pattern = array ('hostname' => '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)' );
	
	/**
	 * Holds an array of errors messages set in this class.
	 * These are used for debugging purposes
	 *
	 * @var array
	 */
	public static $errors = array ();
	
	/**
	 * Checks that a string contains something other than whitespace
	 *
	 * Returns true if string contains something other than whitespace
	 *
	 * $check can be passed as an array:
	 * array('check' => 'valueToCheck');
	 *
	 * @param $check mixed
	 *       	 Value to check
	 * @return boolean Success
	 */
	public static function notEmpty($check) {
		if (is_array ( $check )) {
			extract ( self::_defaults ( $check ) );
		}
		
		if (empty ( $check ) && $check != '0') {
			return false;
		}
		return self::_check ( $check, '/[^\s]+/m' );
	}
	
	/**
	 * Checks that a string contains only integer or letters
	 *
	 * Returns true if string contains only integer or letters
	 *
	 * $check can be passed as an array:
	 * array('check' => 'valueToCheck');
	 *
	 * @param $check mixed
	 *       	 Value to check
	 * @return boolean Success
	 */
	public static function alphaNumeric($check) {
		if (is_array ( $check )) {
			extract ( self::_defaults ( $check ) );
		}
		
		if (empty ( $check ) && $check != '0') {
			return false;
		}
		return self::_check ( $check, '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu' );
	}
	
	/**
	 * Checks that a string length is within s specified range.
	 * Spaces are included in the character count.
	 * Returns true is string matches value min, max, or between min and max,
	 *
	 * @param $check string
	 *       	 Value to check for length
	 * @param $min integer
	 *       	 Minimum value in range (inclusive)
	 * @param $max integer
	 *       	 Maximum value in range (inclusive)
	 * @return boolean Success
	 */
	public static function between($check, $min, $max) {
		$length = mb_strlen ( $check );
		return ($length >= $min && $length <= $max);
	}
	
	/**
	 * Returns true if field is left blank -OR- only whitespace characters are
	 * present in it's value
	 * Whitespace characters include Space, Tab, Carriage Return, Newline
	 *
	 * $check can be passed as an array:
	 * array('check' => 'valueToCheck');
	 *
	 * @param $check mixed
	 *       	 Value to check
	 * @return boolean Success
	 */
	public static function blank($check) {
		if (is_array ( $check )) {
			extract ( self::_defaults ( $check ) );
		}
		return ! self::_check ( $check, '/[^\\s]/' );
	}
	
	/**
	 * Validation of credit card numbers.
	 * Returns true if $check is in the proper credit card format.
	 *
	 * @param $check mixed
	 *       	 credit card number to validate
	 * @param $type mixed
	 *       	 'all' may be passed as a sting, defaults to fast which checks
	 *       	 format of most major credit cards
	 *       	 if an array is used only the values of the array are checked.
	 *       	 Example: array('amex', 'bankcard', 'maestro')
	 * @param $deep boolean
	 *       	 set to true this will check the Luhn algorithm of the credit
	 *       	 card.
	 * @param $regex string
	 *       	 A custom regex can also be passed, this will be used instead
	 *       	 of the defined regex values
	 * @return boolean Success
	 * @see Validation::luhn()
	 */
	public static function cc($check, $type = 'fast', $deep = false, $regex = null) {
		if (is_array ( $check )) {
			extract ( self::_defaults ( $check ) );
		}
		
		$check = str_replace ( array ('-', ' ' ), '', $check );
		if (mb_strlen ( $check ) < 13) {
			return false;
		}
		
		if (! is_null ( $regex )) {
			if (self::_check ( $check, $regex )) {
				return self::luhn ( $check, $deep );
			}
		}
		$cards = array ('all' => array ('amex' => '/^3[4|7]\\d{13}$/', 'bankcard' => '/^56(10\\d\\d|022[1-5])\\d{10}$/', 'diners' => '/^(?:3(0[0-5]|[68]\\d)\\d{11})|(?:5[1-5]\\d{14})$/', 'disc' => '/^(?:6011|650\\d)\\d{12}$/', 'electron' => '/^(?:417500|4917\\d{2}|4913\\d{2})\\d{10}$/', 'enroute' => '/^2(?:014|149)\\d{11}$/', 'jcb' => '/^(3\\d{4}|2100|1800)\\d{11}$/', 'maestro' => '/^(?:5020|6\\d{3})\\d{12}$/', 'mc' => '/^5[1-5]\\d{14}$/', 'solo' => '/^(6334[5-9][0-9]|6767[0-9]{2})\\d{10}(\\d{2,3})?$/', 'switch' => '/^(?:49(03(0[2-9]|3[5-9])|11(0[1-2]|7[4-9]|8[1-2])|36[0-9]{2})\\d{10}(\\d{2,3})?)|(?:564182\\d{10}(\\d{2,3})?)|(6(3(33[0-4][0-9])|759[0-9]{2})\\d{10}(\\d{2,3})?)$/', 'visa' => '/^4\\d{12}(\\d{3})?$/', 'voyager' => '/^8699[0-9]{11}$/' ), 'fast' => '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/' );
		
		if (is_array ( $type )) {
			foreach ( $type as $value ) {
				$regex = $cards ['all'] [strtolower ( $value )];
				
				if (self::_check ( $check, $regex )) {
					return self::luhn ( $check, $deep );
				}
			}
		} elseif ($type == 'all') {
			foreach ( $cards ['all'] as $value ) {
				$regex = $value;
				
				if (self::_check ( $check, $regex )) {
					return self::luhn ( $check, $deep );
				}
			}
		} else {
			$regex = $cards ['fast'];
			
			if (self::_check ( $check, $regex )) {
				return self::luhn ( $check, $deep );
			}
		}
		return false;
	}
	
	/**
	 * Used to compare 2 numeric values.
	 *
	 * @param $check1 mixed
	 *       	 if string is passed for a string must also be passed for
	 *       	 $check2
	 *       	 used as an array it must be passed as array('check1' => value,
	 *       	 'operator' => 'value', 'check2' -> value)
	 * @param $operator string
	 *       	 Can be either a word or operand
	 *       	 is greater >, is less <, greater or equal >=
	 *       	 less or equal <=, is less <, equal to ==, not equal !=
	 * @param $check2 integer
	 *       	 only needed if $check1 is a string
	 * @return boolean Success
	 */
	public static function comparison($check1, $operator = null, $check2 = null) {
		if (is_array ( $check1 )) {
			extract ( $check1, EXTR_OVERWRITE );
		}
		$operator = str_replace ( array (' ', "\t", "\n", "\r", "\0", "\x0B" ), '', strtolower ( $operator ) );
		
		switch ($operator) {
			case 'isgreater' :
			case '>' :
				if ($check1 > $check2) {
					return true;
				}
				break;
			case 'isless' :
			case '<' :
				if ($check1 < $check2) {
					return true;
				}
				break;
			case 'greaterorequal' :
			case '>=' :
				if ($check1 >= $check2) {
					return true;
				}
				break;
			case 'lessorequal' :
			case '<=' :
				if ($check1 <= $check2) {
					return true;
				}
				break;
			case 'equalto' :
			case '==' :
				if ($check1 == $check2) {
					return true;
				}
				break;
			case 'notequal' :
			case '!=' :
				if ($check1 != $check2) {
					return true;
				}
				break;
			default :
				self::$errors [] = sprintf ( 'You must define the $operator parameter for Validation::comparison()' );
				break;
		}
		return false;
	}
	
	/**
	 * Used when a custom regular expression is needed.
	 *
	 * @param $check mixed
	 *       	 When used as a string, $regex must also be a valid regular
	 *       	 expression.
	 *       	 As and array: array('check' => value, 'regex' => 'valid
	 *       	 regular expression')
	 * @param $regex string
	 *       	 If $check is passed as a string, $regex must also be set to
	 *       	 valid regular expression
	 * @return boolean Success
	 */
	public static function custom($check, $regex = null) {
		if (is_array ( $check )) {
			extract ( self::_defaults ( $check ) );
		}
		if ($regex === null) {
			self::$errors [] = sprintf ( 'You must define a regular expression for Validation::custom()' );
			return false;
		}
		return self::_check ( $check, $regex );
	}
	
	/**
	 * Date validation, determines if the string passed is a valid date.
	 * keys that expect full month, day and year will validate leap years
	 *
	 * @param $check string
	 *       	 a valid date string
	 * @param $format mixed
	 *       	 Use a string or an array of the keys below. Arrays should be
	 *       	 passed as array('dmy', 'mdy', etc)
	 *       	 Keys: dmy 27-12-2006 or 27-12-06 separators can be a space,
	 *       	 period, dash, forward slash
	 *       	 mdy 12-27-2006 or 12-27-06 separators can be a space, period,
	 *       	 dash, forward slash
	 *       	 ymd 2006-12-27 or 06-12-27 separators can be a space, period,
	 *       	 dash, forward slash
	 *       	 dMy 27 December 2006 or 27 Dec 2006
	 *       	 Mdy December 27, 2006 or Dec 27, 2006 comma is optional
	 *       	 My December 2006 or Dec 2006
	 *       	 my 12/2006 separators can be a space, period, dash, forward
	 *       	 slash
	 * @param $regex string
	 *       	 If a custom regular expression is used this is the only
	 *       	 validation that will occur.
	 * @return boolean Success
	 */
	public static function date($check, $format = 'ymd', $regex = null) {
		if (! is_null ( $regex )) {
			return self::_check ( $check, $regex );
		}
		
		$regex ['dmy'] = '%^(?:(?:31(\\/|-|\\.|\\x20)(?:0?[13578]|1[02]))\\1|(?:(?:29|30)(\\/|-|\\.|\\x20)(?:0?[1,3-9]|1[0-2])\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:29(\\/|-|\\.|\\x20)0?2\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\\d|2[0-8])(\\/|-|\\.|\\x20)(?:(?:0?[1-9])|(?:1[0-2]))\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$%';
		$regex ['mdy'] = '%^(?:(?:(?:0?[13578]|1[02])(\\/|-|\\.|\\x20)31)\\1|(?:(?:0?[13-9]|1[0-2])(\\/|-|\\.|\\x20)(?:29|30)\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:0?2(\\/|-|\\.|\\x20)29\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:(?:0?[1-9])|(?:1[0-2]))(\\/|-|\\.|\\x20)(?:0?[1-9]|1\\d|2[0-8])\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$%';
		$regex ['ymd'] = '%^(?:(?:(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\\/|-|\\.|\\x20)(?:0?2\\1(?:29)))|(?:(?:(?:1[6-9]|[2-9]\\d)?\\d{2})(\\/|-|\\.|\\x20)(?:(?:(?:0?[13578]|1[02])\\2(?:31))|(?:(?:0?[1,3-9]|1[0-2])\\2(29|30))|(?:(?:0?[1-9])|(?:1[0-2]))\\2(?:0?[1-9]|1\\d|2[0-8]))))$%';
		$regex ['dMy'] = '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\ (Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\ ((1[6-9]|[2-9]\\d)\\d{2})$/';
		$regex ['Mdy'] = '/^(?:(((Jan(uary)?|Ma(r(ch)?|y)|Jul(y)?|Aug(ust)?|Oct(ober)?|Dec(ember)?)\\ 31)|((Jan(uary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep)(tember)?|(Nov|Dec)(ember)?)\\ (0?[1-9]|([12]\\d)|30))|(Feb(ruary)?\\ (0?[1-9]|1\\d|2[0-8]|(29(?=,?\\ ((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))))\\,?\\ ((1[6-9]|[2-9]\\d)\\d{2}))$/';
		$regex ['My'] = '%^(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)[ /]((1[6-9]|[2-9]\\d)\\d{2})$%';
		$regex ['my'] = '%^(((0[123456789]|10|11|12)([- /.])(([1][9][0-9][0-9])|([2][0-9][0-9][0-9]))))$%';
		
		$format = (is_array ( $format )) ? array_values ( $format ) : array ($format );
		foreach ( $format as $key ) {
			$regex = $regex [$key];
			
			if (self::_check ( $check, $regex ) === true) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Validates a datetime value
	 * All values matching the "date" core validation rule, and the "time" one
	 * will be valid
	 *
	 * @param $check array
	 *       	 Value to check
	 * @param $dateFormat mixed
	 *       	 Format of the date part
	 *       	 Use a string or an array of the keys below. Arrays should be
	 *       	 passed as array('dmy', 'mdy', etc)
	 *       	 ## Keys:
	 *       	
	 *       	 - dmy 27-12-2006 or 27-12-06 separators can be a space,
	 *       	 period, dash, forward slash
	 *       	 - mdy 12-27-2006 or 12-27-06 separators can be a space,
	 *       	 period, dash, forward slash
	 *       	 - ymd 2006-12-27 or 06-12-27 separators can be a space,
	 *       	 period, dash, forward slash
	 *       	 - dMy 27 December 2006 or 27 Dec 2006
	 *       	 - Mdy December 27, 2006 or Dec 27, 2006 comma is optional
	 *       	 - My December 2006 or Dec 2006
	 *       	 - my 12/2006 separators can be a space, period, dash, forward
	 *       	 slash
	 * @param $regex string
	 *       	 Regex for the date part. If a custom regular expression is
	 *       	 used this is the only validation that will occur.
	 * @return boolean True if the value is valid, false otherwise
	 * @see Validation::date
	 * @see Validation::time
	 */
	public static function datetime($check, $dateFormat = 'ymd', $regex = null) {
		$valid = false;
		$parts = explode ( ' ', $check );
		if (! empty ( $parts ) && count ( $parts ) > 1) {
			$time = array_pop ( $parts );
			$date = implode ( ' ', $parts );
			$valid = self::date ( $date, $dateFormat, $regex ) && self::time ( $time );
		}
		return $valid;
	}
	
	/**
	 * Time validation, determines if the string passed is a valid time.
	 * Validates time as 24hr (HH:MM) or am/pm ([H]H:MM[a|p]m)
	 * Does not allow/validate seconds.
	 *
	 * @param $check string
	 *       	 a valid time string
	 * @return boolean Success
	 */
	public static function time($check) {
		return self::_check ( $check, '%^((0?[1-9]|1[012])(:[0-5]\d){0,2} ?([AP]M|[ap]m))$|^([01]\d|2[0-3])(:[0-5]\d){0,2}$%' );
	}
	
	/**
	 * Boolean validation, determines if value passed is a boolean integer or
	 * true/false.
	 *
	 * @param $check string
	 *       	 a valid boolean
	 * @return boolean Success
	 */
	public static function boolean($check) {
		$booleanList = array (0, 1, '0', '1', true, false );
		return in_array ( $check, $booleanList, true );
	}
	
	/**
	 * Checks that a value is a valid decimal.
	 * If $places is null, the $check is allowed to be a scientific float
	 * If no decimal point is found a false will be returned. Both the sign and
	 * exponent are optional.
	 *
	 * @param $check integer
	 *       	 The value the test for decimal
	 * @param $places integer
	 *       	 if set $check value must have exactly $places after the
	 *       	 decimal point
	 * @param $regex string
	 *       	 If a custom regular expression is used this is the only
	 *       	 validation that will occur.
	 * @return boolean Success
	 */
	public static function decimal($check, $places = null, $regex = null) {
		if (is_null ( $regex )) {
			if (is_null ( $places )) {
				$regex = '/^[-+]?[0-9]*\\.{1}[0-9]+(?:[eE][-+]?[0-9]+)?$/';
			} else {
				$regex = '/^[-+]?[0-9]*\\.{1}[0-9]{' . $places . '}$/';
			}
		}
		return self::_check ( $check, $regex );
	}
	
	/**
	 * Validates for an email address.
	 *
	 * Only uses getmxrr() checking for deep validation if PHP 5.3.0+ is used,
	 * or
	 * any PHP version on a non-windows distribution
	 *
	 * @param $check string
	 *       	 Value to check
	 * @param $deep boolean
	 *       	 Perform a deeper validation (if true), by also checking
	 *       	 availability of host
	 * @param $regex string
	 *       	 Regex to use (if none it will use built in regex)
	 * @return boolean Success
	 */
	public static function email($check, $deep = false, $regex = null) {
		if (is_array ( $check )) {
			extract ( self::_defaults ( $check ) );
		}
		
		if (is_null ( $regex )) {
			$regex = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@' . self::$_pattern ['hostname'] . '$/i';
		}
		$return = self::_check ( $check, $regex );
		if ($deep === false || $deep === null) {
			return $return;
		}
		
		if ($return === true && preg_match ( '/@(' . self::$_pattern ['hostname'] . ')$/i', $check, $regs )) {
			if (function_exists ( 'getmxrr' ) && getmxrr ( $regs [1], $mxhosts )) {
				return true;
			}
			if (function_exists ( 'checkdnsrr' ) && checkdnsrr ( $regs [1], 'MX' )) {
				return true;
			}
			return is_array ( gethostbynamel ( $regs [1] ) );
		}
		return false;
	}
	
	/**
	 * Check that value is exactly $comparedTo.
	 *
	 * @param $check mixed
	 *       	 Value to check
	 * @param $comparedTo mixed
	 *       	 Value to compare
	 * @return boolean Success
	 */
	public static function equalTo($check, $comparedTo) {
		return ($check === $comparedTo);
	}
	
	/**
	 * Check that value has a valid file extension.
	 *
	 * @param $check mixed
	 *       	 Value to check
	 * @param $extensions array
	 *       	 file extenstions to allow
	 * @return boolean Success
	 */
	public static function extension($check, $extensions = array('gif', 'jpeg', 'png', 'jpg')) {
		if (is_array ( $check )) {
			return self::extension ( array_shift ( $check ), $extensions );
		}
		$pathSegments = explode ( '.', $check );
		$extension = strtolower ( array_pop ( $pathSegments ) );
		foreach ( $extensions as $value ) {
			if ($extension == strtolower ( $value )) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Validation of an IP address.
	 *
	 * @param $check string
	 *       	 The string to test.
	 * @param $type string
	 *       	 The IP Protocol version to validate against
	 * @return boolean Success
	 */
	public static function ip($check, $type = 'both') {
		$type = strtolower ( $type );
		$flags = array ();
		if ($type === 'ipv4' || $type === 'both') {
			$flags [] = FILTER_FLAG_IPV4;
		}
		if ($type === 'ipv6' || $type === 'both') {
			$flags [] = FILTER_FLAG_IPV6;
		}
		return ( boolean ) filter_var ( $check, FILTER_VALIDATE_IP, array ('flags' => $flags ) );
	}
	
	/**
	 * Checks whether the length of a string is greater or equal to a minimal
	 * length.
	 *
	 * @param $check string
	 *       	 The string to test
	 * @param $min integer
	 *       	 The minimal string length
	 * @return boolean Success
	 */
	public static function minLength($check, $min) {
		return mb_strlen ( $check ) >= $min;
	}
	
	/**
	 * Checks whether the length of a string is smaller or equal to a maximal
	 * length..
	 *
	 * @param $check string
	 *       	 The string to test
	 * @param $max integer
	 *       	 The maximal string length
	 * @return boolean Success
	 */
	public static function maxLength($check, $max) {
		return mb_strlen ( $check ) <= $max;
	}
	
	/**
	 * Checks that a value is a monetary amount.
	 *
	 * @param $check string
	 *       	 Value to check
	 * @param $symbolPosition string
	 *       	 Where symbol is located (left/right)
	 * @return boolean Success
	 */
	public static function money($check, $symbolPosition = 'left') {
		$money = '(?!0,?\d)(?:\d{1,3}(?:([, .])\d{3})?(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{2})?';
		if ($symbolPosition == 'right') {
			$regex = '/^' . $money . '(?<!\x{00a2})\p{Sc}?$/u';
		} else {
			$regex = '/^(?!\x{00a2})\p{Sc}?' . $money . '$/u';
		}
		return self::_check ( $check, $regex );
	}
	
	/**
	 * Validate a multiple select.
	 *
	 * Valid Options
	 *
	 * - in => provide a list of choices that selections must be made from
	 * - max => maximum number of non-zero choices that can be made
	 * - min => minimum number of non-zero choices that can be made
	 *
	 * @param $check mixed
	 *       	 Value to check
	 * @param $options mixed
	 *       	 Options for the check.
	 * @return boolean Success
	 */
	public static function multiple($check, $options = array()) {
		$defaults = array ('in' => null, 'max' => null, 'min' => null );
		$options = array_merge ( $defaults, $options );
		$check = array_filter ( ( array ) $check );
		if (empty ( $check )) {
			return false;
		}
		if ($options ['max'] && count ( $check ) > $options ['max']) {
			return false;
		}
		if ($options ['min'] && count ( $check ) < $options ['min']) {
			return false;
		}
		if ($options ['in'] && is_array ( $options ['in'] )) {
			foreach ( $check as $val ) {
				if (! in_array ( $val, $options ['in'] )) {
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * Checks if a value is numeric.
	 *
	 * @param $check string
	 *       	 Value to check
	 * @return boolean Success
	 */
	public static function numeric($check) {
		return is_numeric ( $check );
	}
	
	/**
	 * Check that a value is a valid phone number.
	 *
	 * @param $check mixed
	 *       	 Value to check (string or array)
	 * @param $regex string
	 *       	 Regular expression to use
	 * @param $country string
	 *       	 Country code (defaults to 'all')
	 * @return boolean Success
	 */
	public static function phone($check, $regex = null, $country = 'all') {
		if (is_array ( $check )) {
			extract ( self::_defaults ( $check ) );
		}
		
		if (is_null ( $regex )) {
			switch ($country) {
				case 'us' :
				case 'all' :
				case 'can' :
					// includes all NANPA members. see
					// http://en.wikipedia.org/wiki/North_American_Numbering_Plan#List_of_NANPA_countries_and_territories
					$regex = '/^(?:\+?1)?[-. ]?\\(?[2-9][0-8][0-9]\\)?[-. ]?[2-9][0-9]{2}[-. ]?[0-9]{4}$/';
					break;
			}
		}
		if (empty ( $regex )) {
			return self::_pass ( 'phone', $check, $country );
		}
		return self::_check ( $check, $regex );
	}
	
	/**
	 * Checks that a given value is a valid postal code.
	 *
	 * @param $check mixed
	 *       	 Value to check
	 * @param $regex string
	 *       	 Regular expression to use
	 * @param $country string
	 *       	 Country to use for formatting
	 * @return boolean Success
	 */
	public static function postal($check, $regex = null, $country = 'us') {
		if (is_array ( $check )) {
			extract ( self::_defaults ( $check ) );
		}
		
		if (is_null ( $regex )) {
			switch ($country) {
				case 'uk' :
					$regex = '/\\A\\b[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}\\b\\z/i';
					break;
				case 'ca' :
					$regex = '/\\A\\b[ABCEGHJKLMNPRSTVXY][0-9][A-Z] [0-9][A-Z][0-9]\\b\\z/i';
					break;
				case 'it' :
				case 'de' :
					$regex = '/^[0-9]{5}$/i';
					break;
				case 'be' :
					$regex = '/^[1-9]{1}[0-9]{3}$/i';
					break;
				case 'us' :
					$regex = '/\\A\\b[0-9]{5}(?:-[0-9]{4})?\\b\\z/i';
					break;
			}
		}
		if (empty ( $regex )) {
			return self::_pass ( 'postal', $check, $country );
		}
		return self::_check ( $check, $regex );
	}
	
	/**
	 * Validate that a number is in specified range.
	 * if $lower and $upper are not set, will return true if
	 * $check is a legal finite on this platform
	 *
	 * @param $check string
	 *       	 Value to check
	 * @param $lower integer
	 *       	 Lower limit
	 * @param $upper integer
	 *       	 Upper limit
	 * @return boolean Success
	 */
	public static function range($check, $lower = null, $upper = null) {
		if (! is_numeric ( $check )) {
			return false;
		}
		if (isset ( $lower ) && isset ( $upper )) {
			return ($check > $lower && $check < $upper);
		}
		return is_finite ( $check );
	}
	
	/**
	 * Checks that a value is a valid Social Security Number.
	 *
	 * @param $check mixed
	 *       	 Value to check
	 * @param $regex string
	 *       	 Regular expression to use
	 * @param $country string
	 *       	 Country
	 * @return boolean Success
	 */
	public static function ssn($check, $regex = null, $country = null) {
		if (is_array ( $check )) {
			extract ( self::_defaults ( $check ) );
		}
		
		if (is_null ( $regex )) {
			switch ($country) {
				case 'dk' :
					$regex = '/\\A\\b[0-9]{6}-[0-9]{4}\\b\\z/i';
					break;
				case 'nl' :
					$regex = '/\\A\\b[0-9]{9}\\b\\z/i';
					break;
				case 'us' :
					$regex = '/\\A\\b[0-9]{3}-[0-9]{2}-[0-9]{4}\\b\\z/i';
					break;
			}
		}
		if (empty ( $regex )) {
			return self::_pass ( 'ssn', $check, $country );
		}
		return self::_check ( $check, $regex );
	}
	
	/**
	 * Checks that a value is a valid URL according to
	 * http://www.w3.org/Addressing/URL/url-spec.txt
	 *
	 * The regex checks for the following component parts:
	 *
	 * - a valid, optional, scheme
	 * - a valid ip address OR
	 * a valid domain name as defined by section 2.3.1 of
	 * http://www.ietf.org/rfc/rfc1035.txt
	 * with an optional port number
	 * - an optional valid path
	 * - an optional query string (get parameters)
	 * - an optional fragment (anchor tag)
	 *
	 * @param $check string
	 *       	 Value to check
	 * @param $strict boolean
	 *       	 Require URL to be prefixed by a valid scheme (one of
	 *       	 http(s)/ftp(s)/file/news/gopher)
	 * @return boolean Success
	 */
	public static function url($check, $strict = false) {
		self::_populateIp ();
		$validChars = '([' . preg_quote ( '!"$&\'()*+,-.@_:;=~[]' ) . '\/0-9a-z\p{L}\p{N}]|(%[0-9a-f]{2}))';
		$regex = '/^(?:(?:https?|ftps?|file|news|gopher):\/\/)' . (! empty ( $strict ) ? '' : '?') . '(?:' . self::$_pattern ['IPv4'] . '|\[' . self::$_pattern ['IPv6'] . '\]|' . self::$_pattern ['hostname'] . ')(?::[1-9][0-9]{0,4})?' . '(?:\/?|\/' . $validChars . '*)?' . '(?:\?' . $validChars . '*)?' . '(?:#' . $validChars . '*)?$/iu';
		return self::_check ( $check, $regex );
	}
	
	/**
	 * Checks if a value is in a given list.
	 *
	 * @param $check string
	 *       	 Value to check
	 * @param $list array
	 *       	 List to check against
	 * @return boolean Success
	 */
	public static function inList($check, $list) {
		return in_array ( $check, $list );
	}
	
	/**
	 * Runs an user-defined validation.
	 *
	 * @param $check mixed
	 *       	 value that will be validated in user-defined methods.
	 * @param $object object
	 *       	 class that holds validation method
	 * @param $method string
	 *       	 class method name for validation to run
	 * @param $args array
	 *       	 arguments to send to method
	 * @return mixed user-defined class class method returns
	 */
	public static function userDefined($check, $object, $method, $args = null) {
		return call_user_func_array ( array ($object, $method ), array ($check, $args ) );
	}
	
	/**
	 * Checks that a value is a valid uuid - http://tools.ietf.org/html/rfc4122
	 *
	 * @param $check string
	 *       	 Value to check
	 * @return boolean Success
	 */
	public static function uuid($check) {
		$regex = '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i';
		return self::_check ( $check, $regex );
	}
	
	/**
	 * Attempts to pass unhandled Validation locales to a class starting with
	 * $classPrefix
	 * and ending with Validation.
	 * For example $classPrefix = 'nl', the class would be
	 * `NlValidation`.
	 *
	 * @param $method string
	 *       	 The method to call on the other class.
	 * @param $check mixed
	 *       	 The value to check or an array of parameters for the method to
	 *       	 be called.
	 * @param $classPrefix string
	 *       	 The prefix for the class to do the validation.
	 * @return mixed Return of Passed method, false on failure
	 */
	protected static function _pass($method, $check, $classPrefix) {
		$className = ucwords ( $classPrefix ) . 'Validation';
		if (! class_exists ( $className )) {
			trigger_error ( sprintf ( 'Could not find %s class, unable to complete validation.', $className ), E_USER_WARNING );
			return false;
		}
		if (! method_exists ( $className, $method )) {
			trigger_error ( sprintf ( 'Method %s does not exist on %s unable to complete validation.', $method, $className ), E_USER_WARNING );
			return false;
		}
		$check = ( array ) $check;
		return call_user_func_array ( array ($className, $method ), $check );
	}
	
	/**
	 * Runs a regular expression match.
	 *
	 * @param $check mixed
	 *       	 Value to check against the $regex expression
	 * @param $regex string
	 *       	 Regular expression
	 * @return boolean Success of match
	 */
	protected static function _check($check, $regex) {
		if (preg_match ( $regex, $check )) {
			self::$errors [] = false;
			return true;
		} else {
			self::$errors [] = true;
			return false;
		}
	}
	
	/**
	 * Get the values to use when value sent to validation method is
	 * an array.
	 *
	 * @param $params array
	 *       	 Parameters sent to validation method
	 * @return void
	 */
	protected static function _defaults($params) {
		self::_reset ();
		$defaults = array ('check' => null, 'regex' => null, 'country' => null, 'deep' => false, 'type' => null );
		$params = array_merge ( $defaults, $params );
		if ($params ['country'] !== null) {
			$params ['country'] = mb_strtolower ( $params ['country'] );
		}
		return $params;
	}
	
	/**
	 * Luhn algorithm
	 *
	 * @param $check string|array       	
	 * @param $deep boolean       	
	 * @return boolean Success
	 * @see http://en.wikipedia.org/wiki/Luhn_algorithm
	 */
	public static function luhn($check, $deep = false) {
		if (is_array ( $check )) {
			extract ( self::_defaults ( $check ) );
		}
		if ($deep !== true) {
			return true;
		}
		if ($check == 0) {
			return false;
		}
		$sum = 0;
		$length = strlen ( $check );
		
		for($position = 1 - ($length % 2); $position < $length; $position += 2) {
			$sum += $check [$position];
		}
		
		for($position = ($length % 2); $position < $length; $position += 2) {
			$number = $check [$position] * 2;
			$sum += ($number < 10) ? $number : $number - 9;
		}
		
		return ($sum % 10 == 0);
	}
	
	/**
	 * Lazily populate the IP address patterns used for validations
	 *
	 * @return void
	 */
	protected static function _populateIp() {
		if (! isset ( self::$_pattern ['IPv6'] )) {
			$pattern = '((([0-9A-Fa-f]{1,4}:){7}(([0-9A-Fa-f]{1,4})|:))|(([0-9A-Fa-f]{1,4}:){6}';
			$pattern .= '(:|((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})';
			$pattern .= '|(:[0-9A-Fa-f]{1,4})))|(([0-9A-Fa-f]{1,4}:){5}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})';
			$pattern .= '(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)';
			$pattern .= '{4}(:[0-9A-Fa-f]{1,4}){0,1}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))';
			$pattern .= '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){3}(:[0-9A-Fa-f]{1,4}){0,2}';
			$pattern .= '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|';
			$pattern .= '((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){2}(:[0-9A-Fa-f]{1,4}){0,3}';
			$pattern .= '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))';
			$pattern .= '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)(:[0-9A-Fa-f]{1,4})';
			$pattern .= '{0,4}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)';
			$pattern .= '|((:[0-9A-Fa-f]{1,4}){1,2})))|(:(:[0-9A-Fa-f]{1,4}){0,5}((:((25[0-5]|2[0-4]';
			$pattern .= '\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4})';
			$pattern .= '{1,2})))|(((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})))(%.+)?';
			
			self::$_pattern ['IPv6'] = $pattern;
		}
		if (! isset ( self::$_pattern ['IPv4'] )) {
			$pattern = '(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])';
			self::$_pattern ['IPv4'] = $pattern;
		}
	}
	
	/**
	 * Reset internal variables for another validation run.
	 *
	 * @return void
	 */
	protected static function _reset() {
		self::$errors = array ();
	}

}