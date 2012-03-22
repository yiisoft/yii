<?php
Yii::import("system.web.*");
/**
 * Tests for the {@link CDataProviderIterator} class
 * @author Charles Pick
 */
class CDataProviderIteratorTest extends CTestCase
{
	/**
	 * Tests the iterator
	 */
	public function testIterator()
	{
		$dataProvider = new CArrayDataProvider($this->generateData());
		$iterator = new CDataProviderIterator($dataProvider);
		$this->assertEquals(100, $iterator->getTotalItems());
		$this->assertEquals(100, count($iterator));
		$n = 0;
		foreach($iterator as $item) {
			$this->assertEquals("Item ".$n,$item['name']);
			$n++;

		}
	}

	/**
	 * Generates some data to fill a dataProvider
	 * @param integer $totalItems the total number of items to generate
	 * @return array the data
	 */
	protected function generateData($totalItems = 100)
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