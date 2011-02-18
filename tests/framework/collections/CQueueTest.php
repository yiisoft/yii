<?php

Yii::import('system.collections.CQueue');

class CQueueTest extends CTestCase
{
	public function testConstruct()
	{
		$queue = new CQueue();
		$this->assertEquals(array(), $queue->toArray());
		$queue = new CQueue(array(1, 2, 3));
		$this->assertEquals(array(1, 2, 3), $queue->toArray());
	}

	public function testToArray()
	{
		$queue = new CQueue(array(1, 2, 3));
		$this->assertEquals(array(1, 2, 3), $queue->toArray());
	}

	public function testCopyFrom()
	{
		$queue = new CQueue(array(1, 2, 3));
		$data = array(4, 5, 6);
		$queue->copyFrom($data);
		$this->assertEquals(array(4, 5, 6), $queue->toArray());
	}

	public function testCanNotCopyFromNonTraversableTypes()
	{
		$queue = new CQueue();
		$data = new stdClass();
		$this->setExpectedException('CException');
		$queue->copyFrom($data);
	}

	public function testClear()
	{
		$queue = new CQueue(array(1, 2, 3));
		$queue->clear();
		$this->assertEquals(array(), $queue->toArray());
	}

	public function testContains()
	{
		$queue = new CQueue(array(1, 2, 3));
		$this->assertEquals(true, $queue->contains(2));
		$this->assertEquals(false, $queue->contains(4));
	}

	public function testPeek()
	{
		$queue = new CQueue(array(1));
		$this->assertEquals(1, $queue->peek());
	}

	public function testCanNotPeekAnEmptyQueue()
	{
		$queue = new CQueue();
		$this->setExpectedException('CException');
		$item = $queue->peek();
	}

	public function testDequeue()
	{
		$queue = new CQueue(array(1, 2, 3));
		$first = $queue->dequeue();
		$this->assertEquals(1, $first);
		$this->assertEquals(array(2, 3), $queue->toArray());
	}

	public function testCanNotDequeueAnEmptyQueue()
	{
		$queue = new CQueue();
		$this->setExpectedException('CException');
		$item = $queue->dequeue();
	}

	public function testEnqueue()
	{
		$queue = new CQueue();
		$queue->enqueue(1);
		$this->assertEquals(array(1), $queue->toArray());
	}

 	public function testGetIterator()
 	{
		$queue = new CQueue(array(1, 2));
		$this->assertInstanceOf('CQueueIterator', $queue->getIterator());
		$n = 0;
		$found = 0;
		foreach($queue as $index => $item)
		{
			foreach($queue as $a => $b); // test of iterator
			$n++;
			if($index === 0 && $item === 1)
				$found++;
			if($index === 1 && $item === 2)
				$found++;
		}
		$this->assertTrue($n == 2 && $found == 2);
	}

	public function testGetCount()
	{
    	$queue = new CQueue();
		$this->assertEquals(0, $queue->getCount());
		$queue = new CQueue(array(1, 2, 3));
		$this->assertEquals(3, $queue->getCount());
	}

	public function testCountable()
	{
		$queue = new CQueue();
		$this->assertEquals(0, count($queue));
		$queue = new CQueue(array(1, 2, 3));
		$this->assertEquals(3, count($queue));
	}
}
