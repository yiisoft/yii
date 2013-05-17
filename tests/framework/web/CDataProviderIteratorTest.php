<?php
Yii::import("system.web.*");
/**
 * Tests for the {@link CDataProviderIterator} class
 * @author Charles Pick <charles.pick@gmail.com>
 */
class CDataProviderIteratorTest extends CTestCase
{
	public function pageSizes()
	{
		return array(
			array(null),
			array(1),
			array(10),
			array(110),
		);
	}

	/**
	 * Tests the iterator
	 *
	 * @dataProvider pageSizes
	 */
	public function testIterator($pageSize)
	{
		$dataProvider = new CArrayDataProvider($this->generateData(100));
		$iterator = new CDataProviderIterator($dataProvider, $pageSize);

		$this->assertTrue($iterator->getDataProvider()===$dataProvider);

		$this->assertEquals(100, $iterator->getTotalItemCount());
		$this->assertEquals(100, count($iterator));

		$n = 0;
		foreach($iterator as $item) {
			$this->assertEquals("Item ".$n,$item['name']);
			$n++;
		}

		$this->assertEquals(100, $n);
	}

	/**
	 * @dataProvider pageSizes
	 */
	public function testInitWithDisabledPagination($pageSizes)
	{
		$dataProvider = new CArrayDataProvider($this->generateData(10), array(
			'pagination' => false,
		));
		new CDataProviderIterator($dataProvider, $pageSizes);
	}
	
	/**
	 * Generates some data to fill a dataProvider
	 * @param integer $totalItems the total number of items to generate
	 * @return array the data
	 */
	protected function generateData($totalItems)
	{
		$data = array();
		for($i = 0; $i < $totalItems; $i++) {
			$data[] = array(
				"id" => $i,
				"name" => "Item ".$i,
			);
		}
		return $data;
	}
}