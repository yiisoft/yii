<?php

Yii::import('system.web.CPagination');

/**
 * Unit test for "system.web.CPagination"
 * @see CPagination
 */
class CPaginationTest extends CTestCase
{
	public function testGetCurrentPageFromGet()
	{
		$requestedPageNumber=5;
		$_GET['page']=$requestedPageNumber;
		$pagination=new CPagination(1000);
		$currentPage=$pagination->getCurrentPage();
		$this->assertEquals($requestedPageNumber-1,$currentPage);
	}

	/**
	 * @depends testGetCurrentPageFromGet
	 */
	public function testGetCurrentPageFromCustomRequestParams()
	{
		$requestedPageNumber=4;
		$requestParams=array(
			'page'=>$requestedPageNumber
		);
		$pagination=new CPagination(1000);
		$pagination->setRequestParams($requestParams);
		$currentPage=$pagination->getCurrentPage();
		$this->assertEquals($requestedPageNumber-1,$currentPage);
	}
}
