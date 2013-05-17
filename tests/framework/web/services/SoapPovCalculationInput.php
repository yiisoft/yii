<?php
/**
* Datatype for calculation input
*/
class SoapPovCalculationInput
{
	/**
	* @var integer Subject type (1 = physical person, 2 = juristic person) {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example: 1
	* @soap
	*/
	public $subject;

	/**
	* @var integer Usage type <blockquote>1 - Private usage </br>2 - Taxi<br/>3 - Rental cars</blockquote> {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example: 1
	* @soap
	*/
	public $use_kind;
	
	/**
	* @var integer Engine volume 1 < ccm < 3500 ccm.
	* @example: 1397
	* @soap
	*/
	public $ccm_personal;
	
	/**
	* @var integer Engine power kW > 0 {nillable = 1}
	* @example: 55
	* @soap
	*/
	public $car_power;
	
	/**
	* @var integer Vehicle weight 0 < kg < 3500. {minOccurs = 1, maxOccurs = 1}
	* @example: 1615
	* @soap
	*/
	public $car_kg;
	
	/**
	* @var integer Year made. {nillable = false, minOccurs = 1, maxOccurs = 1}
	* @example: 2001
	* @soap
	*/
	public $production_year;

	/**
	* @var date Vehicle owner - the date of birth RRRR.MM.DD. {nillable = 0, minOccurs = 0, maxOccurs = 1}
	* @example: 1980-12-31
	* @soap
	*/
	public $dob = null;
	
	/**
	* @var date Insurance start date RRRR.MM.DD {nillable = true}
	* @example: 2013-01-01
	* @soap
	*/
	public $ins_start_date;
	
	/**
	* @var string ZIP code of insurer {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example 85HN65
	* @soap
	*/
	public $zip;
	
	/**
	* @var integer Number of previous accidents 0 - 99. {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example: 1
	* @soap
	*/
	public $crash;

	/**
	* @var integer Payment frequency, 1=annually, 2=half year, 4=quarterly. {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example: 2
	* @soap
	*/
	public $payment;
	
	/**
	* @var integer Risk coverage type, 1=lower, 2=higher {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example: 1
	* @soap
	*/
	public $insurance_type;
	
}