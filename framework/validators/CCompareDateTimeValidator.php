<?php
/**
 * CCompareDateTimeValidator class file.
 *
 * @author Mariusz Wyszomierski <wyszomierski.mariusz@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCompareDateTimeValidator compares values, that contain date/time string
 * and validates if they are equal.
 *
 * The value being compared with can be another attribute value
 * (specified via {@link compareAttribute}) or a constant (specified via
 * {@link compareValue}. When both are specified, the latter takes
 * precedence.
 * 
 * Values being compared should be in format that can be parsed by strtotime().
 * See {@link http://www.php.net/manual/en/datetime.formats.php}.
 * 
 * There are flags that specyfied what part of date/time shold be compared.
 * Default CCompareDateTimeValidator compares full date and time.
 * See {@link compareFlag} for more options.
 *
 * CCompareDateTimeValidator supports different comparison operators.
 * Previously, it only compares to see if two dates/times are equal or not.
 *
 * When using the {@link message} property to define a custom error message, the message
 * may contain additional placeholders that will be replaced with the actual content. In addition
 * to the "{attribute}" placeholder, recognized by all validators (see {@link CValidator}),
 * CCompareDateTimeValidator allows for the following placeholders to be specified:
 * <ul>
 * <li>{compareValue}: replaced with the constant value being compared with {@link compareValue}.</li>
 * <li>{compareAttribute}: replaced with the label of compared attribute {@link compareValue}
 * or with the constant value being compared with {@link compareValue}.
 * </li>
 * </ul>
 * 
 * Example of setting CCompareDateTimeValidator to compare only dates
 * <pre>
 * public function rules()
 * {
 *  return array(
 *      array('foo', 'compareDateTime', 'compareAttribute'=>'bar', 'compareFlag'=>CCompareDateTimeValidator::DATE),
 *  );
 * }
 * </pre>
 *
 * @author Mariusz Wyszomierski <wyszomierski.mariusz@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.1.12
 */
class CCompareDateTimeValidator extends CValidator
{
	/**
	 * Marker constant for validator - means compare only seconds
	 */
        const SECOND=1;
	/**
	 * Marker constant for validator - means compare only minutes
	 */
        const MINUTE=2;
	/**
	 * Marker constant for validator - means compare only hours
	 */
        const HOUR=4;
	/**
	 * Marker constant for validator - means compare only days of month
	 */
        const DAY=8;
	/**
	 * Marker constant for validator - means compare only months
	 */
        const MONTH=16;
	/**
	 * Marker constant for validator - means compare only years
	 */
        const YEAR=32;
	/**
	 * Marker constant for validator - means compare only hours and minutes
	 */
        const HOUR_MINUTE=6;
	/**
	 * Marker constant for validator - means compare only times (hours and minutes and seconds)
	 */
        const TIME=7;
	/**
	 * Marker constant for validator - means compare only dates (days and months and years)
	 */
        const DATE=56;
	/**
	 * Marker constant for validator - means compare dates and times
	 */
        const DATE_TIME=63;
        
	/**
	 * @var string the name of the attribute to be compared with
	 */
	public $compareAttribute;
	/**
	 * @var string the constant value to be compared with
	 */
	public $compareValue;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to false.
	 * If this is true, it means the attribute is considered valid when it is empty.
	 */
	public $allowEmpty=false;
	/**
	 * @var string the operator for comparison. Defaults to '='.
	 * The followings are valid operators:
	 * <ul>
	 * <li>'=' or '==': validates to see if the two values are equal. If {@link strict} is true, the comparison
	 * will be done in strict mode (i.e. checking value type as well).</li>
	 * <li>'!=': validates to see if the two values are NOT equal. If {@link strict} is true, the comparison
	 * will be done in strict mode (i.e. checking value type as well).</li>
	 * <li>'>': validates to see if the value being validated is greater than the value being compared with.</li>
	 * <li>'>=': validates to see if the value being validated is greater than or equal to the value being compared with.</li>
	 * <li>'<': validates to see if the value being validated is less than the value being compared with.</li>
	 * <li>'<=': validates to see if the value being validated is less than or equal to the value being compared with.</li>
	 * </ul>
	 */
	public $operator='=';
        /**
         * @var integer defines the flag, that specyfied what part of date/time shold be compared.
         * The followings flags are allowed:
	 * <ul>
	 * <li>CCompareDateTimeValidator::DATE_TIME compares full date and time (year, month, day, hour, minute, second).</li>
	 * <li>CCompareDateTimeValidator::DATE compares only date (year, month, day).</li>
	 * <li>CCompareDateTimeValidator::TIME compares full time (hour, minute, second).</li>
	 * <li>CCompareDateTimeValidator::HOUR_MINUTE compares only hour and minute.</li>
	 * <li>CCompareDateTimeValidator::YEAR compares only year.</li>
	 * <li>CCompareDateTimeValidator::MONTH compares only month.</li>
	 * <li>CCompareDateTimeValidator::DAY compares only day.</li>
         * <li>CCompareDateTimeValidator::HOUR compares only hour.</li>
         * <li>CCompareDateTimeValidator::MINUTE compares only minute.</li>
         * <li>CCompareDateTimeValidator::SECOND compares only second.</li>
	 * </ul>
         */
        public $compareFlag=self::DATE_TIME;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
                $valueTimestamp=strtotime($value);
		if($this->allowEmpty && $this->isEmpty($value)) return;
                else if(!$this->allowEmpty && $this->isEmpty($value))
                {
                    $this->addError($object,$attribute,Yii::t('yii','{attribute} can\'t be empty.'));
                }
                
		if($this->compareValue!==null)
                {
			$compareTo=$compareValue=$this->compareValue;
                        $compareTimestamp=strtotime($compareValue);
                        if(!$compareTimestamp) throw new CException(Yii::t('yii','Date/time to compare "{value}" can\'t be parsed by php function - strtotime().',array('{value}'=>$this->compareValue)));
                }      
		else
		{
			$compareValue=$object->{$this->compareAttribute};
                        $compareTimestamp=strtotime($compareValue);
			$compareTo=$object->getAttributeLabel($this->compareAttribute);
                        if(!$compareTimestamp) $this->addError($object,$this->compareAttribute,Yii::t('yii','{compareAttribute} contains date/time that can\'t be parsed by php function - strtotime()',array('{compareAttribute}'=>$compareTo)));
		}

		switch($this->operator)
		{
			case '=':
			case '==':
				if($this->compareByFlag($valueTimestamp,$compareTimestamp,$this->operator))
				{
					$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be the same.');
					$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo));
				}
				break;
			case '!=':
				if($this->compareByFlag($valueTimestamp,$compareTimestamp,$this->operator))
				{
					$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must not be equal to "{compareValue}".');
					$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo,'{compareValue}'=>$compareValue));
				}
				break;
			case '>':
				if($this->compareByFlag($valueTimestamp,$compareTimestamp,$this->operator))
				{
                                        $message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be greater than "{compareValue}".');
                                        $this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo,'{compareValue}'=>$compareValue));
                                }
				break;
			case '>=':
				if($this->compareByFlag($valueTimestamp,$compareTimestamp,$this->operator))
				{
					$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be greater than or equal to "{compareValue}".');
					$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo,'{compareValue}'=>$compareValue));
				}
				break;
			case '<':
				if($this->compareByFlag($valueTimestamp,$compareTimestamp,$this->operator))
				{
					$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be less than "{compareValue}".');
					$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo,'{compareValue}'=>$compareValue));
				}
				break;
			case '<=':
				if($this->compareByFlag($valueTimestamp,$compareTimestamp,$this->operator))
				{
					$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be less than or equal to "{compareValue}".');
					$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo,'{compareValue}'=>$compareValue));
				}
				break;
			default:
				throw new CException(Yii::t('yii','Invalid operator "{operator}".',array('{operator}'=>$this->operator)));
		}
	}
        
        protected function compareByFlag($valueTimestamp,$compareTimestamp,$operator)
        {
            if($this->compareFlag==self::DATE_TIME)
            {
                if($this->compareTimestamps(strtotime(date("Y-m-d H:i:s",$valueTimestamp)),strtotime(date("Y-m-d H:i:s",$compareTimestamp)),$operator))
                    return true;
            }
            else if($this->compareFlag==self::DATE)
            {
                if($this->compareTimestamps(strtotime(date("Y-m-d",$valueTimestamp)),strtotime(date("Y-m-d",$compareTimestamp)),$operator))
                    return true;  
            }
            else if($this->compareFlag==self::TIME)
            {
                if($this->compareTimestamps(strtotime(date("H:i:s",$valueTimestamp)),strtotime(date("H:i:s",$compareTimestamp)),$operator))
                    return true;  
            }
            else if($this->compareFlag==self::HOUR_MINUTE)
            {
                if($this->compareTimestamps(strtotime(date("H:i",$valueTimestamp)),strtotime(date("H:i",$compareTimestamp)),$operator))
                    return true;  
            }
            else if($this->compareFlag==self::YEAR)
            {
                if($this->compareTimestamps(date("Y",$valueTimestamp),date("Y",$compareTimestamp),$operator))
                    return true;  
            }
            else if($this->compareFlag==self::MONTH)
            {
                if($this->compareTimestamps(date("m",$valueTimestamp),date("m",$compareTimestamp),$operator))
                    return true;  
            }
            else if($this->compareFlag==self::DAY)
            {
                if($this->compareTimestamps(date("d",$valueTimestamp),date("d",$compareTimestamp),$operator))
                    return true;  
            }
            else if($this->compareFlag==self::HOUR)
            {
                if($this->compareTimestamps(date("H",$valueTimestamp),date("H",$compareTimestamp),$operator))
                    return true;  
            }
            else if($this->compareFlag==self::MINUTE)
            {
                if($this->compareTimestamps(date("i",$valueTimestamp),date("i",$compareTimestamp),$operator))
                    return true;  
            }
            else if($this->compareFlag==self::SECOND)
            {
                if($this->compareTimestamps(date("s",$valueTimestamp),date("s",$compareTimestamp),$operator))
                    return true;  
            }
            return false;
        }
        
        protected function compareTimestamps($valueTimestamp,$compareTimestamp,$operator){
                if(($operator=='=' || $operator=='==') && $valueTimestamp!=$compareTimestamp) return true;
                else if(($operator=='!=') && $valueTimestamp==$compareTimestamp) return true;
                else if(($operator=='>') && $valueTimestamp<=$compareTimestamp) return true;
                else if(($operator=='>=') && $valueTimestamp<$compareTimestamp) return true;  
                else if(($operator=='<') && $valueTimestamp>=$compareTimestamp) return true;  
                else if(($operator=='<=') && $valueTimestamp>$compareTimestamp) return true; 
                return false;
        }
}