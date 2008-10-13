<?php
/**
 * CNumberFormatter class file.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CNumberFormatter provides number localization functionalities.
 *
 * CNumberFormatter formats a number (integer or double) and outputs a string
 * based on the specified format. A CNumberFormatter instance is associated with a locale,
 * and thus generates the string representation of the number in a locale-dependent fashion.
 *
 * CNumberFormatter currently supports currency format, percentage format, decimal format,
 * and custom format. The first three formats are specified in the locale data, while the custom
 * format allows you to enter an arbitrary format string.
 *
 * A format string may consist of the following special characters:
 * <ul>
 * <li>dot (.): the decimal point. It will be replaced with the localized decimal point.</li>
 * <li>comma (,): the grouping separator. It will be replaced with the localized grouping separator.</li>
 * <li>zero (0): required digit. This specifies the places where a digit must appear (will pad 0 if not).</li>
 * <li>hash (#): optional digit. This is mainly used to specify the location of decimal point and grouping separators.</li>
 * <li>currency (¤): the currency placeholder. It will be replaced with the localized currency symbol.</li>
 * <li>percentage (%): the percetage mark. If appearing, the number will be multiplied by 100 before being formatted.</li>
 * <li>permillage (‰): the permillage mark. If appearing, the number will be multiplied by 1000 before being formatted.</li>
 * <li>semicolon (;): the character separating positive and negative number sub-patterns.</li>
 * </ul>
 *
 * Anything surrounding the pattern (or sub-patterns) will be kept.
 *
 * The followings are some examples:
 * <pre>
 * Pattern "#,##0.00" will format 12345.678 as "12,345.68".
 * Pattern "#,#,#0.00" will format 12345.6 as "1,2,3,45.60".
 * </pre>
 * Note, in the first example, the number is rounded first before applying the formatting.
 * And in the second example, the pattern specifies two grouping sizes.
 *
 * CNumberFormatter attempts to implement number formatting according to
 * the {@link http://www.unicode.org/reports/tr35/ Unicode Technical Standard #35}.
 * The following features are NOT implemented:
 * <ul>
 * <li>significant digit</li>
 * <li>scientific format</li>
 * <li>arbitrary literal characters</li>
 * <li>arbitrary padding</li>
 * </ul>
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.i18n
 * @since 1.0
 */
class CNumberFormatter extends CComponent
{
	private $_locale;

	/**
	 * Constructor.
	 * @param mixed locale ID (string) or CLocale instance
	 */
	public function __construct($locale)
	{
		if(is_string($locale))
			$this->_locale=CLocale::getInstance($locale);
		else
			$this->_locale=$locale;
	}

	/**
	 * Formats a number based on the specified pattern.
	 * Note, if the format contains '%', the number will be multiplied by 100 first.
	 * If the format contains '‰', the number will be multiplied by 1000.
	 * If the format contains currency placeholder, it will be replaced by
	 * the specified localized currency symbol.
	 * @param string format pattern
	 * @param mixed the number to be formatted
	 * @param string 3-letter ISO 4217 code. For example, the code "USD" represents the US Dollar and "EUR" represents the Euro currency.
	 * The currency placeholder in the pattern will be replaced with the currency symbol.
	 * If null, no replacement will be done.
	 * @return string the formatting result.
	 */
	public function format($pattern,$value,$currency=null)
	{
		$format=$this->parseFormat($pattern);
		$result=$this->formatNumber($format,$value);
		if($currency===null)
			return $result;
		else if(($symbol=$this->_locale->getCurrencySymbol($currency))===null)
			$symbol=$currency;
		return str_replace('¤',$symbol,$result);
	}

	/**
	 * Formats a number using the currency format defined in the locale.
	 * @param mixed the number to be formatted
	 * @param string 3-letter ISO 4217 code. For example, the code "USD" represents the US Dollar and "EUR" represents the Euro currency.
	 * The currency placeholder in the pattern will be replaced with the currency symbol.
	 * @return string the formatting result.
	 */
	public function formatCurrency($value,$currency)
	{
		return $this->format($this->_locale->getCurrencyFormat(),$value,$currency);
	}

	/**
	 * Formats a number using the percentage format defined in the locale.
	 * Note, if the percentage format contains '%', the number will be multiplied by 100 first.
	 * If the percentage format contains '‰', the number will be multiplied by 1000.
	 * @param mixed the number to be formatted
	 * @return string the formatting result.
	 */
	public function formatPercentage($value)
	{
		return $this->format($this->_locale->getPercentFormat(),$value);
	}

	/**
	 * Formats a number using the decimal format defined in the locale.
	 * @param mixed the number to be formatted
	 * @return string the formatting result.
	 */
	public function formatDecimal($value)
	{
		return $this->format($this->_locale->getDecimalFormat(),$value);
	}

	/**
	 * Formats a number based on a format.
	 * This is the method that does actual number formatting.
	 * @param array format with the following structure:
	 * <pre>
	 * array(
	 * 	'decimalDigits'=>2,     // number of required digits after decimal point; if -1, it means we should drop decimal point
	 * 	'integerDigits'=>1,     // number of required digits before decimal point
	 * 	'groupSize1'=>3,        // the primary grouping size; if 0, it means no grouping
	 * 	'groupSize2'=>0,        // the secondary grouping size; if 0, it means no secondary grouping
	 * 	'positivePrefix'=>'+',  // prefix to positive number
	 * 	'positiveSuffix'=>'',   // suffix to positive number
	 * 	'negativePrefix'=>'(',  // prefix to negative number
	 * 	'negativeSuffix'=>')',  // suffix to negative number
	 * 	'multiplier'=>1,        // 100 for percent, 1000 for per mille
	 * );
	 * </pre>
	 * @param mixed the number to be formatted
	 * @return string the formatted result
	 */
	protected function formatNumber($format,$value)
	{
		$negative=$value<0;
		$value=abs($value*$format['multiplier']);
		if($format['decimalDigits']>=0)
			$value=round($value,$format['decimalDigits']);
		list($integer,$decimal)=explode('.',sprintf('%F',$value));

		if($format['decimalDigits']>=0)
		{
			$decimal=rtrim(substr($decimal,0,$format['decimalDigits']),'0');
			$decimal=$this->_locale->getNumberSymbol('decimal').str_pad($decimal,$format['decimalDigits'],'0');
		}
		else
			$decimal='';

		$integer=str_pad($integer,$format['integerDigits'],'0',STR_PAD_LEFT);
		if($format['groupSize1']>0 && strlen($integer)>$format['groupSize1'])
		{
			$str1=substr($integer,0,-$format['groupSize1']);
			$str2=substr($integer,-$format['groupSize1']);
			$size=$format['groupSize2']>0?$format['groupSize2']:$format['groupSize1'];
			$str1=str_pad($str1,(int)((strlen($str1)+$size-1)/$size)*$size,' ',STR_PAD_LEFT);
			$integer=ltrim(implode($this->_locale->getNumberSymbol('group'),str_split($str1,$size))).$this->_locale->getNumberSymbol('group').$str2;
		}

		if($negative)
			$number=$format['negativePrefix'].$integer.$decimal.$format['negativeSuffix'];
		else
			$number=$format['positivePrefix'].$integer.$decimal.$format['positiveSuffix'];

		return strtr($number,array('%'=>$this->_locale->getNumberSymbol('percentSign'),'‰'=>$this->_locale->getNumberSymbol('perMille')));
	}

	/**
	 * Parses a given string pattern.
	 * @param string the pattern to be parsed
	 * @return array the parsed pattern
	 * @see formatNumber
	 */
	protected function parseFormat($pattern)
	{
		static $formats=array();  // cache
		if(isset($formats[$pattern]))
			return $formats[$pattern];

		$format=array();

		// find out prefix and suffix for positive and negative patterns
		$patterns=explode(';',$pattern);
		list($format['positivePrefix'],$format['positiveSuffix'])=preg_split('/[#,\.0]+/',$patterns[0]);
		if(isset($patterns[1]))  // with a negative pattern
			list($format['negativePrefix'],$format['negativeSuffix'])=preg_split('/[#,\.0]+/',$patterns[1]);
		else
		{
			$format['negativePrefix']=$this->_locale->getNumberSymbol('minusSign');
			$format['negativeSuffix']='';
		}
		$pattern=$patterns[0];

		// find out multiplier
		if(strpos($pattern,'%')!==false)
			$format['multiplier']=100;
		else if(strpos($pattern,'‰')!==false)
			$format['multiplier']=1000;
		else
			$format['multiplier']=1;

		// find out things about decimal part
		if(($pos=strpos($pattern,'.'))!==false)
		{
			if(($pos2=strrpos($pattern,'0'))>$pos)
				$format['decimalDigits']=$pos2-$pos;
			else
				$format['decimalDigits']=0;
			$pattern=substr($pattern,0,$pos);
		}
		else   // no decimal part
			$format['decimalDigits']=-1; // do not display decimal point

		// find out things about integer part
		if(($pos=strpos($pattern,'0'))!==false)
			$format['integerDigits']=strlen(str_replace(',','',substr($pattern,$pos)));
		else
			$format['integerDigits']=0;
		// find out group sizes. some patterns may have two different group sizes
		if(($pos=strrpos($pattern,','))!==false)
		{
			$format['groupSize1']=strlen($pattern)-$pos-1;
			if(($pos2=strrpos(substr($pattern,0,$pos),','))!==false)
				$format['groupSize2']=$pos-$pos2-1;
			else
				$format['groupSize2']=0;
		}
		else
			$format['groupSize1']=$format['groupSize2']=0;

		return $formats[$pattern]=$format;
	}
}