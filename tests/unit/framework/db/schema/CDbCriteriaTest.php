<?php
/**
 * CDbCriteriaTest
 */
class CDbCriteriaTest extends CTestCase {
    // TODO: more tests
    function testAddCondition(){
        $criteria = new CDbCriteria();
        $criteria->addCondition('A');
        $criteria->addCondition('B');
        $criteria->addCondition('C', 'OR');
        $this->assertEquals('((A) AND (B)) OR (C)', $criteria->condition);        
    }

    /**
     * @depends testAddCondition
     * @covers CDbCriteria::addInCondition
     */
    function testAddInCondition(){
        // IN with empty array should transform to 0=1        
        // TODO: maybe it's better to use FALSE or just 0 instead of 0=1?
        // WHERE 0=1 equals WHERE 0 equals WHERE FALSE
        $criteria = new CDbCriteria();

        $criteria->addInCondition('A', array());
        $this->assertEquals('0=1', $criteria->condition);
        $this->assertTrue(empty($criteria->params));

        // IN with one parameter should transform to =
        $criteria = new CDbCriteria();

        $criteria->addInCondition('A', array(1));
        $this->assertEquals('A=:ycp0', $criteria->condition);
        $this->assertEquals($criteria->params[':ycp0'], 1);

        // IN with null should transform to IS NULL
        $criteria = new CDbCriteria();

        $criteria->addInCondition('A', array(null));
        $this->assertEquals('A IS NULL', $criteria->condition);
        $this->assertTrue(empty($criteria->params));

        // IN with many parameters
        $criteria = new CDbCriteria();

        $criteria->addInCondition('B', array(1, 2, '3'));
        $this->assertEquals('B IN (:ycp0, :ycp1, :ycp2)', $criteria->condition);
        $this->assertEquals($criteria->params[':ycp0'], 1);
        $this->assertEquals($criteria->params[':ycp1'], 2);
        $this->assertEquals($criteria->params[':ycp2'], '3');
    }

    /**
     * @depends testAddCondition
     * @covers CDbCriteria::addNotInCondition
     */
    function testAddNotInCondition(){
        // NOT IN with empty array should not change anything
        $criteria = new CDbCriteria();

        $criteria->addNotInCondition('A', array());
        $this->assertEquals('', $criteria->condition);
        $this->assertTrue(empty($criteria->params));

        // NOT IN with one parameter should transform to !=
        $criteria = new CDbCriteria();
        
        $criteria->addNotInCondition('A', array(1));
        $this->assertEquals('A!=:ycp0', $criteria->condition);
        $this->assertEquals($criteria->params[':ycp0'], 1);

        // NOT IN with null should transform to IS NOT NULL
        $criteria = new CDbCriteria();

        $criteria->addNotInCondition('A', array(null));
        $this->assertEquals('A IS NOT NULL', $criteria->condition);
        $this->assertTrue(empty($criteria->params));

        // NOT IN with many parameters
        $criteria = new CDbCriteria();
        
        $criteria->addNotInCondition('B', array(1, 2, '3'));
        $this->assertEquals('B NOT IN (:ycp0, :ycp1, :ycp2)', $criteria->condition);
        $this->assertEquals($criteria->params[':ycp0'], 1);
        $this->assertEquals($criteria->params[':ycp1'], 2);
        $this->assertEquals($criteria->params[':ycp2'], '3');
    }
}
