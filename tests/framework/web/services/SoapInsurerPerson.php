<?php
/**
* This is a container for SOAP complex object "insurer".
* It can be either a physical or a juristic person.
* The number of occurences is defined in attribute "insurer" of complex type "SoapPovCalculationInput"
*
* @soap-indicator choice
*/
class SoapInsurerPerson {

	/**
	* @var SoapInsurerPersonPhysical Physical persons.
	* @soap
	*/
	public $insurerPersonPhysical;

	/**
	* @var SoapInsurerPersonJuristic Juristic persons - companies.
	* @soap
	*/
	public $insurerPersonJuristic;

}