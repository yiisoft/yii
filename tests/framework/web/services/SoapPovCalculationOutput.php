<?php
/**
* Datatype for calculation output
*/
class SoapPovCalculationOutput {
	
	/**
	* @var integer calculation ID
	* @example 187
	* @soap
	*/
	public $calculation_id;
	
	/**
	* @var SoapPovCalculationResult[] Caluclation result dataset {nillable = 0, minOccurs = 1, maxOccurs = 1}
	* @soap
	*/
	public $results;

	
	
}