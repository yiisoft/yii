<?php

    Yii::import('system.web.CArrayDataProvider');

class CArrayDataProviderTest extends CTestCase
{
    public function testGetData() {
        $simple_array = array('zero', 'one');
        $dataprovider = new CArrayDataProvider($simple_array);
        $this->assertEquals($simple_array, $dataprovider->getData());
    }

    public function testGetSortedData() {
        $simple_array = array(array('sort_field' => 1), array('sort_field' => 0));
        $dataprovider = new CArrayDataProvider(
            $simple_array,
            array(
                'sort' => array(
                    'attributes' => array(
                        'sort' => array(
                            'asc'=>'sort_field ASC',
                            'desc'=>'sort_field DESC',
                            'label'=>'Sorting',
                            'default'=>'asc',
                        ),
                    ),
                    'defaultOrder'=>array(
                        'sort'=>CSort::SORT_ASC,
                    )
                ),
            )
        );
        $sorted_array = array(array('sort_field' => 0), array('sort_field' => 1));
        $this->assertEquals($sorted_array, $dataprovider->getData());
    }

    public function testGetSortedDataByInnerArrayField(){
        $simple_array = array(
            array('inner_array' => array('sort_field' => 1)),
            array('inner_array' => array('sort_field' => 0))
        );
        $dataprovider = new CArrayDataProvider(
            $simple_array,
            array(
                'sort' => array(
                    'attributes' => array(
                        'sort' => array(
                            'asc'=>'inner_array.sort_field ASC',
                            'desc'=>'inner_array.sort_field DESC',
                            'label'=>'Sorting',
                            'default'=>'asc',
                        ),
                    ),
                    'defaultOrder'=>array(
                        'sort'=>CSort::SORT_ASC,
                    )
                ),
            )
        );
        $sorted_array = array(
            array('inner_array' => array('sort_field' => 0)),
            array('inner_array' => array('sort_field' => 1))
        );
        $this->assertEquals($sorted_array, $dataprovider->getData());
    }
}