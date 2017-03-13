<?php
/**
 * CDateTimeParser class file
 *
 * @author Wei Zhuo <weizhuo[at]gamil[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Tomasz Suchanek <tomasz[dot]suchanek[at]gmail[dot]com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDateTimeParser converts a date/time string to a UNIX timestamp according to the specified pattern.
 *
 * The following pattern characters are recognized:
 * <pre>
 * Pattern |      Description
 * ----------------------------------------------------
 * d       | Day of month 1 to 31, no padding
 * dd      | Day of month 01 to 31, zero leading
 * M       | Month digit 1 to 12, no padding
 * MM      | Month digit 01 to 12, zero leading
 * MMM     | Abbreviation representation of month (available since 1.1.11; locale aware since 1.1.13)
 * MMMM    | Full name representation (available since 1.1.13; locale aware)
 * EEEE    | Full name representation of weekday (available since 1.1.15; locale aware)
 * cccc    | Full name representation of weekday (available since 1.1.15; locale aware)
 * y       | 4 year digit, e.g., 2005 (available since 1.1.16)
 * yy      | 2 year digit, e.g., 96, 05
 * yyyy    | 4 year digit, e.g., 2005
 * h       | Hour in 0 to 12, no padding
 * hh      | Hour in 00 to 12, zero leading
 * H       | Hour in 0 to 23, no padding
 * HH      | Hour in 00 to 23, zero leading
 * m       | Minutes in 0 to 59, no padding
 * mm      | Minutes in 00 to 59, zero leading
 * s       | Seconds in 0 to 59, no padding
 * ss      | Seconds in 00 to 59, zero leading
 * a       | AM or PM, case-insensitive (since version 1.1.5)
 * G       | AD or BC (since version 1.1.15)
 * ?       | matches any character (wildcard) (since version 1.1.11)
 * ----------------------------------------------------
 * </pre>
 * All other characters must appear in the date string at the corresponding positions.
 *
 * For example, to parse a date string '21/10/2008', use the following:
 * <pre>
 * $timestamp=CDateTimeParser::parse('21/10/2008','dd/MM/yyyy');
 * </pre>
 *
 * Locale specific patterns such as MMM and MMMM uses {@link CLocale} for retrieving needed information.
 *
 * To format a timestamp to a date string, please use {@link CDateFormatter}.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.utils
 * @since 1.0
 */
class CDateTimeParser
{
	/**
	 * @var boolean whether 'mbstring' PHP extension available. This static property introduced for
	 * the better overall performance of the class functionality. Checking 'mbstring' availability
	 * through static property with predefined status value is much faster than direct calling
	 * of function_exists('...').
	 * Intended for internal use only.
	 * @since 1.1.13
	 */
	private static $_mbstringAvailable;

	/**
	 * Converts a date string to a timestamp.
	 * @param string $value the date string to be parsed
	 * @param string $pattern the pattern that the date string is following
	 * @param array $defaults the default values for year, month, day, hour, minute and second.
	 * The default values will be used in case when the pattern doesn't specify the
	 * corresponding fields. For example, if the pattern is 'MM/dd/yyyy' and this
	 * parameter is array('minute'=>0, 'second'=>0), then the actual minute and second
	 * for the parsing result will take value 0, while the actual hour value will be
	 * the current hour obtained by date('H'). This parameter has been available since version 1.1.5.
	 * @return integer timestamp for the date string. False if parsing fails.
	 */
	public static function parse($value,$pattern='MM/dd/yyyy',$defaults=array())
	{
		if(self::$_mbstringAvailable===null)
			self::$_mbstringAvailable=extension_loaded('mbstring');

		$tokens=self::tokenize($pattern);
		$i=0;
		$n=self::$_mbstringAvailable ? mb_strlen($value,Yii::app()->charset) : strlen($value);
		$skip = false;
		foreach($tokens as $token)
		{
			if ( $token == "'") {
				$skip = !$skip;
				continue;
			}

			if ( $skip ) {
				$i += self::$_mbstringAvailable ? mb_strlen($token,Yii::app()->charset) : strlen($token);
				continue;
			}

			switch($token)
			{
				case 'yyyy':
				case 'y':
				{
					if(($year=self::parseInteger($value,$i,4,4))===false)
						return false;
					$i+=4;
					break;
				}
				case 'yy':
				{
					if(($year=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=self::$_mbstringAvailable ? mb_strlen($year,Yii::app()->charset) : strlen($year);
					break;
				}
				case 'MMMM':
				{
					$monthName='';
					if(($month=self::parseMonth($value,$i,'wide',$monthName))===false)
						return false;
					$i+=self::$_mbstringAvailable ? mb_strlen($monthName,Yii::app()->charset) : strlen($monthName);
					break;
				}
				case 'MMM':
				{
					$monthName='';
					if(($month=self::parseMonth($value,$i,'abbreviated',$monthName))===false)
						return false;
					$i+=self::$_mbstringAvailable ? mb_strlen($monthName,Yii::app()->charset) : strlen($monthName);
					break;
				}
				case 'MM':
				{
					if(($month=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'M':
				{
					if(($month=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=self::$_mbstringAvailable ? mb_strlen($month,Yii::app()->charset) : strlen($month);
					break;
				}
				case 'dd':
				{
					if(($day=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'd':
				{
					if(($day=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=self::$_mbstringAvailable ? mb_strlen($day,Yii::app()->charset) : strlen($day);
					break;
				}
				case 'h':
				case 'H':
				{
					if(($hour=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=self::$_mbstringAvailable ? mb_strlen($hour,Yii::app()->charset) : strlen($hour);
					break;
				}
				case 'hh':
				case 'HH':
				{
					if(($hour=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'm':
				{
					if(($minute=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=self::$_mbstringAvailable ? mb_strlen($minute,Yii::app()->charset) : strlen($minute);
					break;
				}
				case 'mm':
				{
					if(($minute=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 's':
				{
					if(($second=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=self::$_mbstringAvailable ? mb_strlen($second,Yii::app()->charset) : strlen($second);
					break;
				}
				case 'ss':
				{
					if(($second=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'a':
				{
					if(($ampm=self::parseAmPm($value,$i))===false) {
						return false;
					}
					if(isset($hour))
					{
						if($hour==12 && $ampm==='am')
							$hour=0;
						elseif($hour<12 && $ampm==='pm')
							$hour+=12;
					}
					$i+=2;
					break;
				}
				case 'EEEE':
				case 'cccc':
				{
					$weekdayName='';
					if(($weekday=self::parseWeekday($value,$i,'wide',$weekdayName))===false)
						return false;

					$i+=self::$_mbstringAvailable ? mb_strlen($weekdayName,Yii::app()->charset) : strlen($weekdayName);
					break;
				}
				case 'G':
				{
					$i+=2;
					break;
				}
				default:
				{
					$tn=self::$_mbstringAvailable ? mb_strlen($token,Yii::app()->charset) : strlen($token);
					if($i>=$n || ($token{0}!='?' && (self::$_mbstringAvailable ? mb_substr($value,$i,$tn,Yii::app()->charset) : substr($value,$i,$tn))!==$token))
						return false;

					$i+=$tn;
					break;
				}
			}
		}
		if($i<$n)
			return false;


		if(!isset($year))
			$year=isset($defaults['year']) ? $defaults['year'] : date('Y');
		if(!isset($month))
			$month=isset($defaults['month']) ? $defaults['month'] : date('n');
		if(!isset($day))
			$day=isset($defaults['day']) ? $defaults['day'] : date('j');

		if((self::$_mbstringAvailable ? mb_strlen($year,Yii::app()->charset) : $year($second))===2)
		{
			if($year>=70)
				$year+=1900;
			else
				$year+=2000;
		}
		$year=(int)$year;
		$month=(int)$month;
		$day=(int)$day;

		if(
			!isset($hour) && !isset($minute) && !isset($second)
			&& !isset($defaults['hour']) && !isset($defaults['minute']) && !isset($defaults['second'])
		)
			$hour=$minute=$second=0;
		else
		{
			if(!isset($hour))
				$hour=isset($defaults['hour']) ? $defaults['hour'] : date('H');
			if(!isset($minute))
				$minute=isset($defaults['minute']) ? $defaults['minute'] : date('i');
			if(!isset($second))
				$second=isset($defaults['second']) ? $defaults['second'] : date('s');
			$hour=(int)$hour;
			$minute=(int)$minute;
			$second=(int)$second;
		}

		if(CTimestamp::isValidDate($year,$month,$day) && CTimestamp::isValidTime($hour,$minute,$second))
			return CTimestamp::getTimestamp($hour,$minute,$second,$month,$day,$year);
		else
			return false;
	}

	/*
	 * @param string $pattern the pattern that the date string is following
	 */
	private static function tokenize($pattern)
	{
		if(!($n=self::$_mbstringAvailable ? mb_strlen($pattern, Yii::app()->charset) : strlen($pattern)))
			return array();
		$tokens=array();
		$c0=self::$_mbstringAvailable ? mb_substr($pattern, 0, 1, Yii::app()->charset) : substr($pattern, 0, 1);
		for($start=0,$i=1;$i<$n;++$i)
		{
			$c = self::$_mbstringAvailable ? mb_substr($pattern, $i, 1, Yii::app()->charset) : substr($pattern, $i, 1);
			if($c!==$c0)
			{
				$tokens[]=self::$_mbstringAvailable ? mb_substr($pattern, $start, $i-$start,Yii::app()->charset) : substr($pattern,$start,$i-$start);
				$c0=$c;
				$start=$i;
			}
		}
		$tokens[]=self::$_mbstringAvailable ? mb_substr($pattern, $start, $n-$start,Yii::app()->charset) : substr($pattern,$start,$n-$start);
		return $tokens;
	}

	/**
	 * @param string $value the date string to be parsed
	 * @param integer $offset starting offset
	 * @param integer $minLength minimum length
	 * @param integer $maxLength maximum length
	 * @return string parsed integer value
	 */
	protected static function parseInteger($value,$offset,$minLength,$maxLength)
	{
		for($len=$maxLength;$len>=$minLength;--$len)
		{
			$v=self::$_mbstringAvailable ? mb_substr($value,$offset,$len,Yii::app()->charset) : substr($value,$offset,$len);
			if(ctype_digit($v) && (self::$_mbstringAvailable ? mb_strlen($v,Yii::app()->charset) : strlen($v))>=$minLength)
				return $v;
		}
		return false;
	}

	/**
	 * @param string $value the date string to be parsed
	 * @param integer $offset starting offset
	 * @return string parsed day period value
	 */
	protected static function parseAmPm($value, $offset)
	{
		$v=strtolower(self::$_mbstringAvailable ? mb_substr($value,$offset,2,Yii::app()->charset) : substr($value,$offset,2));
		return $v==='am' || $v==='pm' ? $v : false;
	}

	/**
	 * @param string $value the date string to be parsed.
	 * @param integer $offset starting offset.
	 * @param string $width month name width. It can be 'wide', 'abbreviated' or 'narrow'.
	 * @param string $monthName extracted month name. Passed by reference.
	 * @return string parsed month name.
	 * @since 1.1.13
	 */
	protected static function parseMonth($value,$offset,$width,&$monthName)
	{
		$valueLength=self::$_mbstringAvailable ? mb_strlen($value,Yii::app()->charset) : strlen($value);
		for($len=1; $offset+$len<=$valueLength; $len++)
		{
			$monthName=self::$_mbstringAvailable ? mb_substr($value,$offset,$len,Yii::app()->charset) : substr($value,$offset,$len);
			if(!preg_match('/^[\p{L}\p{M}]+$/u',$monthName)) // unicode aware replacement for ctype_alpha($monthName)
			{
				$monthName=self::$_mbstringAvailable ? mb_substr($monthName,0,-1,Yii::app()->charset) : substr($monthName,0,-1);
				break;
			}
		}
		$monthName=self::$_mbstringAvailable ? mb_strtolower($monthName,Yii::app()->charset) : strtolower($monthName);

		$monthNames=Yii::app()->getLocale()->getMonthNames($width,false);
		foreach($monthNames as $k=>$v)
			$monthNames[$k]=rtrim(self::$_mbstringAvailable ? mb_strtolower($v,Yii::app()->charset) : strtolower($v),'.');

		$monthNamesStandAlone=Yii::app()->getLocale()->getMonthNames($width,true);
		foreach($monthNamesStandAlone as $k=>$v)
			$monthNamesStandAlone[$k]=rtrim(self::$_mbstringAvailable ? mb_strtolower($v,Yii::app()->charset) : strtolower($v),'.');

		if(($v=self::array_find($monthName,$monthNames))===false) {
			if (($v=self::array_find($monthName,$monthNamesStandAlone))===false)
				return false;

			$monthName = $monthNamesStandAlone[$v];
		} else {
			$monthName = $monthNames[$v];
		}

		return $v;
	}

	/**
	 * @param string $value the date string to be parsed.
	 * @param integer $offset starting offset.
	 * @param string $width week day name width. It can be 'wide', 'abbreviated' or 'narrow'.
	 * @param string $weekdayName extracted week day name. Passed by reference.
	 * @return string parsed week day name.
	 * @since 1.1.15
	 */
	protected static function parseWeekday($value,$offset,$width,&$weekdayName)
	{
		$valueLength=self::$_mbstringAvailable ? mb_strlen($value,Yii::app()->charset) : strlen($value);
		for($len=1; $offset+$len<=$valueLength; $len++)
		{
			$weekdayName=self::$_mbstringAvailable ? mb_substr($value,$offset,$len,Yii::app()->charset) : substr($value,$offset,$len);
			if(!preg_match('/^\p{L}+$/u',$weekdayName)) // unicode aware replacement for ctype_alpha($weekdayName)
			{
				$weekdayName=self::$_mbstringAvailable ? mb_substr($weekdayName,0,-1,Yii::app()->charset) : substr($weekdayName,0,-1);
				break;
			}
		}
		$weekdayName=self::$_mbstringAvailable ? mb_strtolower($weekdayName,Yii::app()->charset) : strtolower($weekdayName);

		$weekdayNames=Yii::app()->getLocale()->getWeekDayNames($width,false);
		foreach($weekdayNames as $k=>$v)
			$weekdayNames[$k]=rtrim(self::$_mbstringAvailable ? mb_strtolower($v,Yii::app()->charset) : strtolower($v),'.');

		$weekdayNamesStandAlone=Yii::app()->getLocale()->getWeekDayNames($width,true);
		foreach($weekdayNamesStandAlone as $k=>$v)
			$weekdayNamesStandAlone[$k]=rtrim(self::$_mbstringAvailable ? mb_strtolower($v,Yii::app()->charset) : strtolower($v),'.');

		if(($v=self::array_find($weekdayName,$weekdayNames))===false) {
			if (($v=self::array_find($weekdayName,$weekdayNamesStandAlone))===false)
				return false;

			$weekdayName = $weekdayNamesStandAlone[$v];
		} else {
			$weekdayName = $weekdayNames[$v];
		}

		return $v;
	}

	/**
	 * searches for a matching string in $haystack with starts with $needle
	 *
	 * @param string $needle
	 * @param string[] $haystack
	 * @return string|false matching string or false if nothing could be found
	 */
	protected static function array_find($needle, array $haystack)
	{
		foreach ($haystack as $key => $value) {
			if (0 === (self::$_mbstringAvailable ? mb_stripos($needle, $value, 0, Yii::app()->charset) : stripos($needle, $value))) {
				return $key;
			}
		}
		return false;
	}
}
