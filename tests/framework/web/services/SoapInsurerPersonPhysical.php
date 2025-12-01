<?php
/**
* SOAP WSDL description of a physical person.
* In this example:
* - We want for each person to know either an age and date of birth.
* - We optionally want to know, whether a person is a student.
*
* Following custom piece of WSDL code will be inserted instead of automatically generated XML nodes.
* This will efficiently enable defining SOAP validations of any complexity.
*
* @soap-wsdl <xsd:sequence>
* @soap-wsdl 	<xsd:choice minOccurs="1" maxOccurs="1" nillable="false">
* @soap-wsdl 		<xsd:element minOccurs="1" maxOccurs="1" nillable="false" name="age" type="xsd:integer"/>
* @soap-wsdl 		<xsd:element minOccurs="1" maxOccurs="1" nillable="false" name="date_of_birth" type="xsd:date"/>
* @soap-wsdl 	</xsd:choice>
* @soap-wsdl 	<xsd:element minOccurs="0" maxOccurs="1" nillable="false" name="student" type="xsd:boolean"/>
* @soap-wsdl 	<xsd:element minOccurs="0" maxOccurs="unbounded" nillable="false" name="studentCardNumber" type="xsd:boolean"/>
* @soap-wsdl </xsd:sequence>
*/
class SoapInsurerPersonPhysical {

	/**
	* @var integer Insurer age {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example 35
	* @soap
	*/
	public $age;

	/**
	* @var date Date of birth {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @example 1980-05-27
	* @soap
	*/
	public $date_of_birth;

	/**
	* @var boolean Is insurer a student? (must have age between 17 - 16 years incl.) {nillable = 0, minOccurs = 0, maxOccurs = 1}
	* @example true
	* @soap
	*/
	public $student = false;

	/**
	* @var string Serial number of any held student card. {minOccurs = 0, maxOccurs = unbounded}
	* @example GO26-26110801B
	* @soap
	*/
	public $studentCardNumber;

}