<?php
/**
 * CDbCriteriaTest
 */
class CDbCriteriaTest extends CTestCase {
	/**
	 * @covers CDbCriteria::addCondition
	 */
	function testAddCondition() {
		//adding new condition to empty one
		$criteria = new CDbCriteria();
		$criteria->addCondition('A');
		$this->assertEquals('A', $criteria->condition);

		//adding multiple conditions
		$criteria = new CDbCriteria();
		$criteria->addCondition('A');
		$criteria->addCondition('B');
		$criteria->addCondition('C', 'OR');
		$this->assertEquals('((A) AND (B)) OR (C)', $criteria->condition);

		//adding empty array as condition
		$criteria = new CDbCriteria();
		$criteria->addCondition('A');
		$criteria->addCondition(array());
		$this->assertEquals('A', $criteria->condition);

		//adding array as condition
		$criteria = new CDbCriteria();
		$criteria->addCondition(array('A', 'B'));
		$this->assertEquals('(A) AND (B)', $criteria->condition);
	}

	/**
	 * @depends testAddCondition
	 * @covers CDbCriteria::addInCondition
	 */
	function testAddInCondition() {
		$criteria = new CDbCriteria();

		$criteria->addInCondition('A', array());
		$this->assertEquals('0=1', $criteria->condition);
		$this->assertTrue(empty($criteria->params));

		// IN with one parameter should transform to =
		$criteria = new CDbCriteria();

		$criteria->addInCondition('A', array(1));
		$this->assertEquals('A=:ycp0', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);

		// IN with null should transform to IS NULL
		$criteria = new CDbCriteria();

		$criteria->addInCondition('A', array(null));
		$this->assertEquals('A IS NULL', $criteria->condition);
		$this->assertTrue(empty($criteria->params));

		// IN with many parameters
		$criteria = new CDbCriteria();

		$criteria->addInCondition('B', array(1, 2, '3'));
		$this->assertEquals('B IN (:ycp0, :ycp1, :ycp2)', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);
		$this->assertEquals(2, $criteria->params[':ycp1']);
		$this->assertEquals('3', $criteria->params[':ycp2']);
	}

	/**
	 * @depends testAddCondition
	 * @covers CDbCriteria::addNotInCondition
	 */
	function testAddNotInCondition() {
		// NOT IN with empty array should not change anything
		$criteria = new CDbCriteria();

		$criteria->addNotInCondition('A', array());
		$this->assertEquals('', $criteria->condition);
		$this->assertTrue(empty($criteria->params));

		// NOT IN with one parameter should transform to !=
		$criteria = new CDbCriteria();

		$criteria->addNotInCondition('A', array(1));
		$this->assertEquals('A!=:ycp0', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);

		// NOT IN with null should transform to IS NOT NULL
		$criteria = new CDbCriteria();

		$criteria->addNotInCondition('A', array(null));
		$this->assertEquals('A IS NOT NULL', $criteria->condition);
		$this->assertTrue(empty($criteria->params));

		// NOT IN with many parameters
		$criteria = new CDbCriteria();

		$criteria->addNotInCondition('B', array(1, 2, '3'));
		$this->assertEquals('B NOT IN (:ycp0, :ycp1, :ycp2)', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);
		$this->assertEquals(2, $criteria->params[':ycp1']);
		$this->assertEquals('3', $criteria->params[':ycp2']);
	}

	/**
	 * @depends testAddCondition
	 * @covers CDbCriteria::addSearchCondition
	 */
	function testAddSearchCondition() {
		// string escaping
		$criteria = new CDbCriteria();
		$criteria->addSearchCondition('A', 'key_word%');

		$this->assertEquals('A LIKE :ycp0', $criteria->condition);
		$this->assertEquals('%key\_word\%%', $criteria->params[':ycp0']);

		// no escaping
		$criteria = new CDbCriteria();
		$criteria->addSearchCondition('A', 'key_word%', false);

		$this->assertEquals('A LIKE :ycp0', $criteria->condition);
		$this->assertEquals('key_word%', $criteria->params[':ycp0']);
	}

	/**
	 * @depends testAddCondition
	 * @covers CDbCriteria::addColumnCondition
	 */
	function testAddColumnCondition() {		
		$criteria = new CDbCriteria();
		$criteria->addColumnCondition(array('A' => 1, 'B' => null, 'C' => '2'));

		$this->assertEquals('A=:ycp0 AND B IS NULL AND C=:ycp1', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);
		$this->assertEquals('2', $criteria->params[':ycp1']);
	}

	/**
	 * @depends testAddCondition
	 * @covers CDbCriteria::compare
	 */
	function testCompare(){
		$criteria = new CDbCriteria();
		$criteria->compare('A', '');
		$this->assertEquals('', $criteria->condition);		

		$criteria = new CDbCriteria();
		$criteria->compare('A', 1);
		$this->assertEquals('A=:ycp0', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '>1');
		$this->assertEquals('A>:ycp0', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '<1');
		$this->assertEquals('A<:ycp0', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '<=1');
		$this->assertEquals('A<=:ycp0', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '>=1');
		$this->assertEquals('A>=:ycp0', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '<>1');
		$this->assertEquals('A<>:ycp0', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '=1');
		$this->assertEquals('A=:ycp0', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '1', true);
		$this->assertEquals('A LIKE :ycp0', $criteria->condition);
		$this->assertEquals('%1%', $criteria->params[':ycp0']);
		
		$criteria = new CDbCriteria();
		$criteria->compare('A', '=1', true);
		$this->assertEquals('A LIKE :ycp0', $criteria->condition);
		$this->assertEquals('%1%', $criteria->params[':ycp0']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '<>1', true);
		$this->assertEquals('A NOT LIKE :ycp0', $criteria->condition);
		$this->assertEquals('%1%', $criteria->params[':ycp0']);
	}

	/**
	 * @ depends testCompare
	 * @covers CDbCriteria::mergeWith
	 */
	function testMergeWith() {
		// merging select

		// * should be replaced
		$criteria1 = new CDbCriteria;
		$criteria1->select = '*';

		$criteria2 = new CDbCriteria;
		$criteria2->select = 'a';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('a', $criteria1->select);

		// equal selects should be left as is
		$criteria1 = new CDbCriteria;
		$criteria1->select = 'a';

		$criteria2 = new CDbCriteria;
		$criteria2->select = 'a';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('a', $criteria1->select);

		// not equal selects are being merged
		$criteria1 = new CDbCriteria;
		$criteria1->select = 'a, b, c, d';

		$criteria2 = new CDbCriteria;
		$criteria2->select = 'a, c, e, f';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals(array('a', 'b', 'c', 'd', 'e', 'f'), $criteria1->select);

		// conditions

		// equal conditions are not merged
		$criteria1 = new CDbCriteria;
		$criteria1->condition = 'a';

		$criteria2 = new CDbCriteria;
		$criteria2->condition = 'a';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('a', $criteria1->condition);

		// empty condition is being replaced
		$criteria1 = new CDbCriteria;
		$criteria1->condition = '';

		$criteria2 = new CDbCriteria;
		$criteria2->condition = 'a';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('a', $criteria1->condition);

		// not empty conditions are merged
		$criteria1 = new CDbCriteria;
		$criteria1->condition = 'a';

		$criteria2 = new CDbCriteria;
		$criteria2->condition = 'b';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('(a) AND (b)', $criteria1->condition);		

		// limit, offset, distinct and alias are being replaced
		$criteria1 = new CDbCriteria;
		$criteria1->limit = 10;
		$criteria1->offset = 5;
		$criteria1->alias = 'alias1';
		$criteria1->distinct = true;

		$criteria2 = new CDbCriteria;
		$criteria2->limit = 20;
		$criteria2->offset = 6;
		$criteria2->alias = 'alias2';
		$criteria1->distinct = false;

		$criteria1->mergeWith($criteria2);

		$this->assertEquals(20, $criteria1->limit);
		$this->assertEquals(6, $criteria1->offset);
		$this->assertEquals('alias2', $criteria1->alias);
		$this->assertEquals(false, $criteria1->distinct);


		// empty order, group, join, having are being replaced
		$criteria1 = new CDbCriteria;
		$criteria1->order = '';
		$criteria1->group = '';
		$criteria1->join = '';
		$criteria1->having = '';

		$criteria2 = new CDbCriteria;
		$criteria2->order = 'a';
		$criteria1->group = 'a';
		$criteria1->join = 'a';
		$criteria2->having = 'a';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('a', $criteria1->order);
		$this->assertEquals('a', $criteria1->group);
		$this->assertEquals('a', $criteria1->join);
		$this->assertEquals('a', $criteria1->having);

		// merging with empty order, group, join ignored
		$criteria1 = new CDbCriteria;
		$criteria1->order = 'a';
		$criteria1->group = 'a';
		$criteria1->join = 'a';
		$criteria1->having = 'a';

		$criteria2 = new CDbCriteria;
		$criteria2->order = '';
		$criteria2->group = '';
		$criteria2->join = '';
		$criteria2->having = '';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('a', $criteria1->order);
		$this->assertEquals('a', $criteria1->group);
		$this->assertEquals('a', $criteria1->join);
		$this->assertEquals('a', $criteria1->having);

		// not empty order, group, join are being merged
		$criteria1 = new CDbCriteria;
		$criteria1->order = 'a';
		$criteria1->group = 'a';
		$criteria1->join = 'a';
		$criteria1->having = 'a';

		$criteria2 = new CDbCriteria;
		$criteria2->order = 'b';
		$criteria2->group = 'b';
		$criteria2->join = 'b';
		$criteria2->having = 'b';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('b, a', $criteria1->order);
		$this->assertEquals('a, b', $criteria1->group);
		$this->assertEquals('a b', $criteria1->join);
		$this->assertEquals('(a) AND (b)', $criteria1->having);

		// empty with is replaced
		$criteria1 = new CDbCriteria;
		$criteria1->with = '';

		$criteria2 = new CDbCriteria;
		$criteria2->with = 'a';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('a', $criteria1->with);

		// not empty with are merged
		$criteria1 = new CDbCriteria;
		$criteria1->with = array('a', 'b');

		$criteria2 = new CDbCriteria;
		$criteria2->with = array('a', 'c');

		$criteria1->mergeWith($criteria2);

		// TODO: shouldn't it produce 'a', 'b', 'c'?
		$this->assertEquals(array('a', 'b', 'a', 'c'), $criteria1->with);		

		// merging two criteria with parameters
		$criteria1 = new CDbCriteria;
		$criteria1->compare('A', 1);

		$criteria2 = new CDbCriteria;
		$criteria2->compare('B', 2);

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('(A=:ycp0) AND (B=:ycp1)', $criteria1->condition);
		$this->assertEquals(1, $criteria1->params[':ycp0']);
		$this->assertEquals(2, $criteria1->params[':ycp1']);
	}
}
