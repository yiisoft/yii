<?php

Yii::import('system.web.CArrayDataProvider');

class CArrayDataProviderTest extends CTestCase
{
	public function testGetData()
	{
		$simpleArray = array('zero', 'one');
		$dataProvider = new CArrayDataProvider($simpleArray);
		$this->assertEquals($simpleArray, $dataProvider->getData());
	}

	public function testGetSortedData()
	{
		$simpleArray = array(array('sortField' => 1), array('sortField' => 0));
		$dataProvider = new CArrayDataProvider(
			$simpleArray,
			array(
				'sort' => array(
					'attributes' => array(
						'sort' => array(
							'asc' => 'sortField ASC',
							'desc' => 'sortField DESC',
							'label' => 'Sorting',
							'default' => 'asc',
						),
					),
					'defaultOrder' => array(
						'sort' => CSort::SORT_ASC,
					)
				),
			)
		);
		$sortedArray = array(array('sortField' => 0), array('sortField' => 1));
		$this->assertEquals($sortedArray, $dataProvider->getData());
	}

	public function testGetSortedDataByInnerArrayField()
	{
		$simpleArray = array(
			array('innerArray' => array('sortField' => 1)),
			array('innerArray' => array('sortField' => 0))
		);
		$dataProvider = new CArrayDataProvider(
			$simpleArray,
			array(
				'sort' => array(
					'attributes' => array(
						'sort' => array(
							'asc' => 'innerArray.sortField ASC',
							'desc' => 'innerArray.sortField DESC',
							'label' => 'Sorting',
							'default' => 'asc',
						),
					),
					'defaultOrder' => array(
						'sort' => CSort::SORT_ASC,
					)
				),
			)
		);
		$sortedArray = array(
			array('innerArray' => array('sortField' => 0)),
			array('innerArray' => array('sortField' => 1))
		);
		$this->assertEquals($sortedArray, $dataProvider->getData());
	}
}