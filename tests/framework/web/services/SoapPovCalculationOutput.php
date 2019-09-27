<?php
/**
* Datatype for calculation output.
*/
class SoapPovCalculationOutput {

	/**
	* @var int calculation ID
	* @example 187
	* @soap
	*/
	public $calculation_id;

	/**
	* @var SoapPovCalculationResult[] Calculation result dataset
	* @soap
	*/
	public $results;



}