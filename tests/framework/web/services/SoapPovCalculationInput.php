<?php
/**
* By default, WSDL generator assumes, that the order of object attributes in a complex type does not matter.
* In this object however, we want to have all attributes supplied in an expected order - thus we must set the soap indicator flag "sequence" for the WSDL generator.
*
* @soap-indicator sequence
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
	* @var integer Usage type <blockquote>1 - Private usage <br/>2 - Taxi<br/>3 - Rental cars</blockquote> {nillable = 0, minOccurs = 1, maxOccurs = 1}
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

	/**
	* This variable tests various flexibility requirements on WSDL definitions, such as soap indicators (choice, sequence) and inserting any custom defined XML nodes into WSDL file.
	* We want an insurer to be either a juristic or a physical person. There must be always at least 1 person and maximum 10 persons supplied.
	* A juristic person is defined in class "SoapInsurerPersonJuristic" and physical person in class "SoapInsurerPersonPhysical".
	*
	* @var SoapInsurerPerson List of insured persons. It can be either a physical person or a juristic person  {nillable = 0, minOccurs = 1, maxOccurs = 10}
	* @soap
	*/
	public $insurer;

}