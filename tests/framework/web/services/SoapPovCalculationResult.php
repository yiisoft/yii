<?php
/**
* Data structure returned from SOAP call calculate
*/
class SoapPovCalculationResult {

	/**
	* @var string Insurance company key {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example allianz
	* @soap
	*/
	public $company_key;

	/**
	* @var string Product name {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example AllRisk
	* @soap
	*/
	public $product_name;

	/**
	* @var string Insurance company full name {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example Allianz Ltd.
	* @soap
	*/
	public $company_fullname;

	/**
	* @var float Coverage for health mil. EUR {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example 1.5
	* @soap
	*/
	public $coverage_health;

	/**
	* @var float Coverage for property in mil. EUR {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example 1.2
	* @soap
	*/
	public $coverage_property;

	/**
	* @var integer Insurance premium EUR {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example 145
	* @soap
	*/
	public $premium;

}