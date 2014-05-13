<?php
/**
 * CDbCriteriaTest
 */
class CDbCriteriaTest extends CTestCase {
	protected $backupStaticAttributes = true;

	/**
	 * @covers CDbCriteria::addCondition
	 */
	public function testAddCondition() {
		//adding new condition to empty one
		CDbCriteria::$paramCount=0;
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
	public function testAddInCondition() {
		CDbCriteria::$paramCount=0;
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
		$this->assertEquals('B IN (:ycp1, :ycp2, :ycp3)', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp1']);
		$this->assertEquals(2, $criteria->params[':ycp2']);
		$this->assertEquals('3', $criteria->params[':ycp3']);
	}

	/**
	 * @depends testAddCondition
	 * @covers CDbCriteria::addNotInCondition
	 */
	public function testAddNotInCondition() {
		// NOT IN with empty array should not change anything
		CDbCriteria::$paramCount=0;
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
		$this->assertEquals('B NOT IN (:ycp1, :ycp2, :ycp3)', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp1']);
		$this->assertEquals(2, $criteria->params[':ycp2']);
		$this->assertEquals('3', $criteria->params[':ycp3']);
	}

	/**
	 * @depends testAddCondition
	 * @covers CDbCriteria::addSearchCondition
	 */
	public function testAddSearchCondition() {
		// string escaping
		CDbCriteria::$paramCount=0;
		$criteria = new CDbCriteria();
		$criteria->addSearchCondition('A', 'key_word%');

		$this->assertEquals('A LIKE :ycp0', $criteria->condition);
		$this->assertEquals('%key\_word\%%', $criteria->params[':ycp0']);

		// no escaping
		$criteria = new CDbCriteria();
		$criteria->addSearchCondition('A', 'key_word%', false);

		$this->assertEquals('A LIKE :ycp1', $criteria->condition);
		$this->assertEquals('key_word%', $criteria->params[':ycp1']);
	}

	/**
	 * @depends testAddCondition
	 * @covers CDbCriteria::addColumnCondition
	 */
	public function testAddColumnCondition() {
		CDbCriteria::$paramCount=0;
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
	public function testCompare(){
		CDbCriteria::$paramCount=0;
		$criteria = new CDbCriteria();
		$criteria->compare('A', '');
		$this->assertEquals('', $criteria->condition);

		$criteria = new CDbCriteria();
		$criteria->compare('A', 1);
		$this->assertEquals('A=:ycp0', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '>1');
		$this->assertEquals('A>:ycp1', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp1']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '<1');
		$this->assertEquals('A<:ycp2', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp2']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '<=1');
		$this->assertEquals('A<=:ycp3', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp3']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '>=1');
		$this->assertEquals('A>=:ycp4', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp4']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '<>1');
		$this->assertEquals('A<>:ycp5', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp5']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '=1');
		$this->assertEquals('A=:ycp6', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp6']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '1', true);
		$this->assertEquals('A LIKE :ycp7', $criteria->condition);
		$this->assertEquals('%1%', $criteria->params[':ycp7']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '=1', true);
		$this->assertEquals('A=:ycp8', $criteria->condition);
		$this->assertEquals('1', $criteria->params[':ycp8']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '<>1', true);
		$this->assertEquals('A NOT LIKE :ycp9', $criteria->condition);
		$this->assertEquals('%1%', $criteria->params[':ycp9']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', '    value_with_spaces  ');
		$this->assertEquals('A=:ycp10', $criteria->condition);
		$this->assertEquals('    value_with_spaces  ', $criteria->params[':ycp10']);

		$criteria = new CDbCriteria();
		$criteria->compare('A', array());
		$this->assertEquals('', $criteria->condition);

		$criteria = new CDbCriteria();
		$criteria->compare('A', array(1, '2'));
		$this->assertEquals('A IN (:ycp11, :ycp12)', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp11']);
		$this->assertEquals('2', $criteria->params[':ycp12']);
	}

	/**
	 * @depends testCompare
	 * @covers CDbCriteria::mergeWith
	 */
	public function testMergeWith() {
		// merging select

		// * should be replaced
		CDbCriteria::$paramCount=0;
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
		$this->assertFalse($criteria1->distinct);


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
		$criteria1->with = 'a';

		$criteria2 = new CDbCriteria;
		$criteria2->with = 'b';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals(array('a', 'b'), $criteria1->with);

		// not empty with are merged (more complex test)
		$criteria1 = new CDbCriteria;
		$criteria1->with = array('a', 'b');

		$criteria2 = new CDbCriteria;
		$criteria2->with = array('a', 'c');

		$criteria1->mergeWith($criteria2);

		$this->assertEquals(array('a', 'b', 'a', 'c'), $criteria1->with);

		// merging scopes
		$criteria1=new CDbCriteria;
		$criteria1->scopes='scope1';

		$criteria2=new CDbCriteria;
		$criteria2->scopes='scope2';

		$criteria1->mergeWith($criteria2);

		$this->assertEquals(array('scope1','scope2'),$criteria1->scopes);

		$criteria1=new CDbCriteria;
		$criteria1->scopes='scope1';

		$criteria2=new CDbCriteria;
		$criteria2->scopes=array('scope2'=>1);

		$criteria1->mergeWith($criteria2);

		$this->assertEquals(array('scope1','scope2'=>1),$criteria1->scopes);

		$criteria1=new CDbCriteria;
		$criteria1->scopes=array('scope1'=>array(1,2));

		$criteria2=new CDbCriteria;
		$criteria2->scopes=array('scope2'=>array(3,4));

		$criteria1->mergeWith($criteria2);

		$this->assertEquals(array('scope1'=>array(1,2),'scope2'=>array(3,4)),$criteria1->scopes);

		$criteria1=new CDbCriteria;
		$criteria1->scopes=array('scope'=>array(1,2));

		$criteria2=new CDbCriteria;
		$criteria2->scopes=array('scope'=>array(3,4));

		$criteria1->mergeWith($criteria2);

		$this->assertEquals(array(array('scope'=>array(1,2)),array('scope'=>array(3,4))),$criteria1->scopes);

		$criteria1=new CDbCriteria;
		$criteria1->scopes=array('scope'=>array(1,2),'scope1');

		$criteria2=new CDbCriteria;
		$criteria2->scopes=array('scope2','scope'=>array(3,4));

		$criteria1->mergeWith($criteria2);

		$this->assertEquals(array(array('scope'=>array(1,2)),'scope1','scope2',array('scope'=>array(3,4))),$criteria1->scopes);

		$criteria1=new CDbCriteria;
		$criteria1->scopes=array(array('scope'=>array(1,2)),array('scope'=>array(3,4)));

		$criteria2=new CDbCriteria;
		$criteria2->scopes=array(array('scope'=>array(5,6)),array('scope'=>array(7,8)));

		$criteria1->mergeWith($criteria2);

		$this->assertEquals(array(array('scope'=>array(1,2)),array('scope'=>array(3,4)),array('scope'=>array(5,6)),array('scope'=>array(7,8))),$criteria1->scopes);

		// merging two criteria with parameters
		$criteria1 = new CDbCriteria;
		$criteria1->compare('A1', 1);
		$criteria1->compare('A2', 2);
		$criteria1->compare('A3', 3);
		$criteria1->compare('A4', 4);
		$criteria1->compare('A5', 5);
		$criteria1->compare('A6', 6);

		$criteria2 = new CDbCriteria;
		$criteria2->compare('B1', 7);
		$criteria2->compare('B2', 8);
		$criteria2->compare('B3', 9);
		$criteria2->compare('B4', 10);
		$criteria2->compare('B5', 11);
		$criteria2->compare('B6', 12);

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('((((((A1=:ycp0) AND (A2=:ycp1)) AND (A3=:ycp2)) AND (A4=:ycp3)) AND (A5=:ycp4)) AND (A6=:ycp5)) AND ((((((B1=:ycp6) AND (B2=:ycp7)) AND (B3=:ycp8)) AND (B4=:ycp9)) AND (B5=:ycp10)) AND (B6=:ycp11))', $criteria1->condition);
		$this->assertEquals(1, $criteria1->params[':ycp0']);
		$this->assertEquals(2, $criteria1->params[':ycp1']);
		$this->assertEquals(3, $criteria1->params[':ycp2']);
		$this->assertEquals(4, $criteria1->params[':ycp3']);
		$this->assertEquals(5, $criteria1->params[':ycp4']);
		$this->assertEquals(6, $criteria1->params[':ycp5']);
		$this->assertEquals(7, $criteria1->params[':ycp6']);
		$this->assertEquals(8, $criteria1->params[':ycp7']);
		$this->assertEquals(9, $criteria1->params[':ycp8']);
		$this->assertEquals(10, $criteria1->params[':ycp9']);
		$this->assertEquals(11, $criteria1->params[':ycp10']);
		$this->assertEquals(12, $criteria1->params[':ycp11']);
	}

	/**
	 * Merging criterias with positioned and non positioned parameters.
	 *
	 * @depends testCompare
	 * @covers CDbCriteria::mergeWith
	 */
	public function testMergeWithPositionalPlaceholders(){
		CDbCriteria::$paramCount=0;
		$criteria1 = new CDbCriteria();
		$criteria1->condition = 'A=? AND B=?';
		$criteria1->params = array(0 => 10, 1 => 20);

		$criteria2 = new CDbCriteria();
		$criteria2->compare('C', 30);
		$criteria2->compare('D', 40);

		$criteria2->mergeWith($criteria1);

		$this->assertEquals('((C=:ycp0) AND (D=:ycp1)) AND (A=? AND B=?)', $criteria2->condition);

		$this->assertEquals(10, $criteria2->params[0]);
		$this->assertEquals(20, $criteria2->params[1]);
		$this->assertEquals(30, $criteria2->params[':ycp0']);
		$this->assertEquals(40, $criteria2->params[':ycp1']);

		// and vice versa

		$criteria1 = new CDbCriteria();
		$criteria1->condition = 'A=? AND B=?';
		$criteria1->params = array(0 => 10, 1 => 20);

		$criteria2 = new CDbCriteria();
		$criteria2->compare('C', 30);
		$criteria2->compare('D', 40);

		$criteria1->mergeWith($criteria2);

		$this->assertEquals('(A=? AND B=?) AND ((C=:ycp2) AND (D=:ycp3))', $criteria1->condition);
		$this->assertEquals(10, $criteria1->params[0]);
		$this->assertEquals(20, $criteria1->params[1]);
		$this->assertEquals(30, $criteria1->params[':ycp2']);
		$this->assertEquals(40, $criteria1->params[':ycp3']);
	}

	/**
	 * @covers CDbCriteria::addBetweenCondition
	 */
	public function testAddBetweenCondition(){
		CDbCriteria::$paramCount=0;
		$criteria = new CDbCriteria();

		$criteria->addBetweenCondition('A', 1, 2);
		$this->assertEquals('A BETWEEN :ycp0 AND :ycp1', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':ycp0']);
		$this->assertEquals(2, $criteria->params[':ycp1']);
	}

	public function testToArray(){
		$keys = array('select', 'condition', 'params', 'limit', 'offset', 'order', 'group', 'join', 'having', 'distinct', 'scopes', 'with', 'alias', 'index', 'together');
		$criteria = new CDbCriteria();
		$this->assertEquals($keys, array_keys($criteria->toArray()));
	}

	public function testSerialize()
	{
		$criteria = new CDbCriteria();

		$fieldName='testFieldName';
		$paramName=':testParamName';
		$paramValue='testParamValue';
		$criteria->condition="{$fieldName} = {$paramName}";
		$criteria->order="{$paramName}";
		$criteria->group="{$paramName}";
		$criteria->having="{$paramName}";
		$criteria->select="{$paramName}";
		$criteria->params[$paramName]=$paramValue;
		
		$serializedCriteria=serialize($criteria);
		$unserializedCriteria=unserialize($serializedCriteria);

		$this->assertEquals($criteria,$unserializedCriteria,'Criteria has wrong data after wakeup!');
	}

	/**
	 * @depends testSerialize
	 */
	public function testSerializeAutomaticallyGeneratedParams()
	{
		$criteria = new CDbCriteria();
		$paramName=CDbCriteria::PARAM_PREFIX.rand(10000,20000); // mock up automatically generated name
		$paramValue = 'testParamValue';
		$criteria->condition="someField = {$paramName}";
		$criteria->order="{$paramName}";
		$criteria->group="{$paramName}";
		$criteria->having="{$paramName}";
		$criteria->select="{$paramName}";
		$criteria->params[$paramName]=$paramValue;

		$serializedCriteria=serialize($criteria);
		$unserializedCriteria=unserialize($serializedCriteria);

		$this->assertArrayNotHasKey($paramName,$unserializedCriteria->params,'Param name which match automatic generation has not been replaced!');
		$this->assertContains($paramValue,$unserializedCriteria->params,'Automatically generated param value has been lost!');

		$newParamName = array_search($paramValue,$unserializedCriteria->params,true);
		$this->assertEquals(str_replace($paramName,$newParamName,$criteria->condition),$unserializedCriteria->condition,'Criteria condition has not been updated!');
		$this->assertEquals(str_replace($paramName,$newParamName,$criteria->order),$unserializedCriteria->order,'Criteria order has not been updated!');
		$this->assertEquals(str_replace($paramName,$newParamName,$criteria->group),$unserializedCriteria->group,'Criteria group has not been updated!');
		$this->assertEquals(str_replace($paramName,$newParamName,$criteria->having),$unserializedCriteria->having,'Criteria having has not been updated!');
		$this->assertEquals(str_replace($paramName,$newParamName,$criteria->select),$unserializedCriteria->select,'Criteria select has not been updated!');
	}

	/**
	 * https://github.com/yiisoft/yii/issues/2426
	 */
	public function testWakeupWhenSqlContainingFieldsAreArraysWithSpecifiedParams()
	{
		CDbCriteria::$paramCount=10;
		$criteria=new CDbCriteria();
		$criteria->select=array('id','title');
		$criteria->condition='id=:postId';
		$criteria->params['postId']=1;
		$criteria->compare('authorId',2);

		$oldCriteria=clone $criteria;

		$criteria=serialize($criteria);
		CDbCriteria::$paramCount=10;
		$criteria=unserialize($criteria);

		$this->assertEquals($oldCriteria,$criteria);
	}
}
