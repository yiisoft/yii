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

	public function testCaseSensitiveSort()
	{
		// source data
		$unsortedProjects=array(
			array('title'=>'Zabbix', 'license'=>'GPL'),
			array('title'=>'munin', 'license'=>'GPL'),
			array('title'=>'Arch Linux', 'license'=>'GPL'),
			array('title'=>'Nagios', 'license'=>'GPL'),
			array('title'=>'zend framework', 'license'=>'BSD'),
			array('title'=>'Zope', 'license'=>'ZPL'),
			array('title'=>'active-record', 'license'=>false),
			array('title'=>'ActiveState', 'license'=>false),
			array('title'=>'mach', 'license'=>false),
			array('title'=>'MySQL', 'license'=>'GPL'),
			array('title'=>'mssql', 'license'=>'EULA'),
			array('title'=>'Master-Master', 'license'=>false),
			array('title'=>'Zend Engine', 'license'=>false),
			array('title'=>'Mageia Linux', 'license'=>'GNU GPL'),
			array('title'=>'nginx', 'license'=>'BSD'),
			array('title'=>'Mozilla Firefox', 'license'=>'MPL'),
		);

		// expected data
		$sortedProjects=array(
			// upper cased titles
			array('title'=>'ActiveState', 'license'=>false),
			array('title'=>'Arch Linux', 'license'=>'GPL'),
			array('title'=>'Mageia Linux', 'license'=>'GNU GPL'),
			array('title'=>'Master-Master', 'license'=>false),
			array('title'=>'Mozilla Firefox', 'license'=>'MPL'),
			array('title'=>'MySQL', 'license'=>'GPL'),
			array('title'=>'Nagios', 'license'=>'GPL'),
			array('title'=>'Zabbix', 'license'=>'GPL'),
			array('title'=>'Zend Engine', 'license'=>false),
			array('title'=>'Zope', 'license'=>'ZPL'),
			// lower cased titles
			array('title'=>'active-record', 'license'=>false),
			array('title'=>'mach', 'license'=>false),
			array('title'=>'mssql', 'license'=>'EULA'),
			array('title'=>'munin', 'license'=>'GPL'),
			array('title'=>'nginx', 'license'=>'BSD'),
			array('title'=>'zend framework', 'license'=>'BSD'),
		);

		$dataProvider=new CArrayDataProvider($unsortedProjects, array(
			'sort'=>array(
				'attributes'=>array(
					'sort'=>array(
						'asc'=>'title ASC',
						'desc'=>'title DESC',
						'label'=>'Title',
						'default'=>'desc',
					),
				),
				'defaultOrder'=>array(
					'sort'=>CSort::SORT_ASC,
				)
			),
			'pagination'=>array(
				'pageSize'=>100500,
			),
		));

		// $dataProvider->caseSensitiveSort is true by default, so we do not touching it

		$this->assertEquals($sortedProjects, $dataProvider->getData());
	}

	public function testCaseInsensitiveSort()
	{
		// source data
		$unsortedProjects=array(
			array('title'=>'Zabbix', 'license'=>'GPL'),
			array('title'=>'munin', 'license'=>'GPL'),
			array('title'=>'Arch Linux', 'license'=>'GPL'),
			array('title'=>'Nagios', 'license'=>'GPL'),
			array('title'=>'zend framework', 'license'=>'BSD'),
			array('title'=>'Zope', 'license'=>'ZPL'),
			array('title'=>'active-record', 'license'=>false),
			array('title'=>'ActiveState', 'license'=>false),
			array('title'=>'mach', 'license'=>false),
			array('title'=>'MySQL', 'license'=>'GPL'),
			array('title'=>'mssql', 'license'=>'EULA'),
			array('title'=>'Master-Master', 'license'=>false),
			array('title'=>'Zend Engine', 'license'=>false),
			array('title'=>'Mageia Linux', 'license'=>'GNU GPL'),
			array('title'=>'nginx', 'license'=>'BSD'),
			array('title'=>'Mozilla Firefox', 'license'=>'MPL'),
		);

		// expected data
		$sortedProjects=array(
			// case is not taken into account
			array('title'=>'active-record', 'license'=>false),
			array('title'=>'ActiveState', 'license'=>false),
			array('title'=>'Arch Linux', 'license'=>'GPL'),
			array('title'=>'mach', 'license'=>false),
			array('title'=>'Mageia Linux', 'license'=>'GNU GPL'),
			array('title'=>'Master-Master', 'license'=>false),
			array('title'=>'Mozilla Firefox', 'license'=>'MPL'),
			array('title'=>'mssql', 'license'=>'EULA'),
			array('title'=>'munin', 'license'=>'GPL'),
			array('title'=>'MySQL', 'license'=>'GPL'),
			array('title'=>'Nagios', 'license'=>'GPL'),
			array('title'=>'nginx', 'license'=>'BSD'),
			array('title'=>'Zabbix', 'license'=>'GPL'),
			array('title'=>'Zend Engine', 'license'=>false),
			array('title'=>'zend framework', 'license'=>'BSD'),
			array('title'=>'Zope', 'license'=>'ZPL'),
		);

		$dataProvider=new CArrayDataProvider($unsortedProjects, array(
			'sort'=>array(
				'attributes'=>array(
					'sort'=>array(
						'asc'=>'title ASC',
						'desc'=>'title DESC',
						'label'=>'Title',
						'default'=>'desc',
					),
				),
				'defaultOrder'=>array(
					'sort'=>CSort::SORT_ASC,
				)
			),
			'pagination'=>array(
				'pageSize'=>100500,
			),
		));

		// we're explicitly setting case insensitive sort
		$dataProvider->caseSensitiveSort = false;

		$this->assertEquals($sortedProjects, $dataProvider->getData());
	}
}