<?php
/**
 * CNumberSanatizer class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CNumberSanatizer sanatizes an attribute containing a number.
 * 
 * CNumberSanatizer has a couple of custom settings:
 * <ul>
 * <li>{min}: when using {@link tooSmall}, replaced with the lower limit of the number {@link min}.</li>
 * <li>{max}: when using {@link tooBig}, replaced with the upper limit of the number {@link max}.</li>
 * <li>{allowEmpty}: Wether to allow an empty attribute. If this is set to false, the empty attribute is replaced with $emptyValue {@link allowEmpty}.</li>
 * <li>{emptyValue}: If {@link allowEmpty} is set to false this value is used to fill an empty attribute {@link emptyValue}.</li>
 * <li>{to}: Target type of the sanatization. One of the following values: int, uint, float, ufloat {@link to}.</li>
 * <li>{fallBackValue}: Value used if attribute cannot be transformed into a number {@link fallBackValue}.</li>
 * <li>{precision}: precision used while transforming a number to a float {@link max}.</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Suralc <thesurwaveing@googlemail.com>
 * @version $Id$
 * @package system.sanatizers
 * @since 1.1.13
 */
class CNumberSanatizer extends CSanatizer
{
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;
        /**
         * @var integer|float This value is used as a default value if allowEmpty is set to false.
         */
        public $emptyValue=0;
        /**
         * @var string String indicating the target of the sanatization. Possible values: int, uint, float, ufloat 
         */
        public $to='uint';
        /**
         * @var mixed If value cannot be casted or modified this value is used. 
         */
        public $fallBackValue=-1;
        /**
         * @var int|float|string Maximum value of the attribute. If value is higher it is lowered to this value.
         */
        public $max;
        /**
         * @var int|float|string Maximum value of the attribute. If value is higher it is lowered to this value
         */
        public $min;
        /**
         * @var integer Only in use if $to is a float or ufloat 
         */
        public $precision=null;
	/**
	 * @var string the regular expression for matching numbers.
	 */
	public $numberPattern='/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';


	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function sanatizeAttribute($object,$attribute)
	{
                if($this->allowEmpty&&empty($object->$attribute))
                    return true;
                elseif($this->allowEmpty!=true&&empty($object->$attribute))
                {
                    $object->$attribute=$this->emptyValue;
                    return true;
                }
                $value=$object->$attribute;
                if(preg_match($this->numberPattern,$value)&&!is_int($value))
                       $value=floatval($value);
                if(!is_numeric($value))
                {
                    $object->$attribute=$this->fallBackValue;
                }                    
                $value=$this->padToBoundaries($value);
                $unsinged=stripos($this->to,'u')!==false?true:false;
                $type=!$unsinged?$this->to:substr($this->to,1);
                switch((strtolower($type)))
                {
                    case 'int':
                        $value=intval($value);
                        $value=$unsinged?abs($value):$value;
                        break;
                    case 'float':
                        $value=floatval($value);
                        $value=$unsinged?abs($value):$value;
                        if($this->precision!==null)
                            $value=round($value,$this->precision);
                        break;
                    default:
                        throw new CException(Yii::t('yii', 'Type "{type}" is not supported in {class}',
                                array('{type}'=>(string)$this->to,'{class}'=>get_class($this))));
                }
                $object->$attribute=$value;
                return true;
	}
        
        protected function padToBoundaries($value)
        {
            if(is_numeric($this->max))
            {
                if($value>$this->max)
                     $value=$this->max;
            }
            if(is_numeric($this->min)){
                if($value<$this->min)
                    $value=$this->min;
            }
            return $value;
        }
}