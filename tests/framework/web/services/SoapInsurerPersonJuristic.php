<?php
/**
* SOAP definitions for a juristic person.
* We always want to know the name of the company and its tax number.
* Optionally, full address can be also supplied.
* All attributes in any order.
*/
class SoapInsurerPersonJuristic {

	/**
	* @var string First name {minoccurs = 1, maxoccurs = 1, nillable = false}
	* @soap
	* @example TOP-Solutions Ltd.
	*/
	public $companyName;

	/**
	* @var string Company tax number {minoccurs = 1, maxoccurs = 1, nillable = false}
	* @example 112233445BB
	* @soap
	*/
	public $companyTaxNumber;

	/**
	* @var string Company street {minoccurs = 0, maxoccurs = 1, nillable = true}
	* @example Wembley Hill Rd.
	* @soap
	*/
	public $companyAddressStreet;

	/**
	* @var string Company house number {minoccurs = 0, maxoccurs = 1, nillable = true}
	* @example 45/c
	* @soap
	*/
	public $companyAddressHouseNr;

	/**
	* @var string Company ZIP postal code {minoccurs = 0, maxoccurs = 1, nillable = true}
	* @example H7D 99B
	* @soap
	*/
	public $companyAddressZip;


}