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

	public function testNestedObjectsSort()
	{
		$obj1 = new \stdClass();
		$obj1->type = "def";
		$obj1->owner = $obj1;
		$obj2 = new \stdClass();
		$obj2->type = "abc";
		$obj2->owner = $obj2;
		$obj3 = new \stdClass();
		$obj3->type = "abc";
		$obj3->owner = $obj3;
		$models = array($obj1, $obj2, $obj3);

		$this->assertEquals($obj2, $obj3);
		$dataProvider = new CArrayDataProvider($models, array(
			'sort'=>array(
				'attributes'=>array(
					'sort'=>array(
						'asc'=>'type ASC',
						'desc'=>'type DESC',
						'label'=>'Type',
						'default'=>'asc',
					),
				),
				'defaultOrder'=>array(
					'sort'=>CSort::SORT_ASC,
				)
			),
		));
		$sortedArray = array($obj2, $obj3, $obj1);
		$this->assertEquals($sortedArray, $dataProvider->getData());
	}

    public function testCaseInsensitiveSortWithNullValue()
    {
        // This deprecation occurs only on PHP 8.1+
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            $this->markTestSkipped('mb_strtolower(null, ...) deprecation only occurs on PHP 8.1+');
        }

        $previousErrorReporting = error_reporting(E_ALL);
        $previousHandler = set_error_handler(function ($errno, $errstr) {
            if (($errno & (E_DEPRECATED | E_USER_DEPRECATED)) !== 0
                && strpos($errstr, 'mb_strtolower') !== false
            ) {
                throw new ErrorException($errstr, 0, $errno);
            }

            return false;
        });

        $previousErrorReporting = error_reporting(E_ALL);
        $previousHandler = set_error_handler(function ($errno, $errstr) {
            if (($errno & (E_DEPRECATED | E_USER_DEPRECATED)) !== 0
                && strpos($errstr, 'mb_strtolower') !== false
            ) {
                throw new ErrorException($errstr, 0, $errno);
            }

            return false;
        });

        $exception = null;

        try {
            $data = array(
                array('id' => 1, 'name' => 'Alpha'),
                array('id' => 2, 'name' => null),
                array('id' => 3, 'name' => 'beta'),
            );

            $dataProvider = new CArrayDataProvider($data, array(
                'keyField' => 'id',
                'sort' => array(
                    'attributes' => array(
                        'name' => array(
                            'asc' => 'name ASC',
                            'desc' => 'name DESC',
                            'label' => 'Name',
                            'default' => 'asc',
                        ),
                    ),
                    'defaultOrder' => array(
                        'name' => CSort::SORT_ASC,
                    ),
                ),
            ));

            $dataProvider->caseSensitiveSort = false;

            // Before the fix on PHP 8.1+ this triggered a deprecation via mb_strtolower(null, ...)
            $items = $dataProvider->getData();

            $this->assertCount(3, $items);

            $ids = array();
            foreach ($items as $item) {
                $ids[] = $item['id'];
            }
            $this->assertContains(2, $ids);

            foreach ($items as $item) {
                if ($item['id'] === 2) {
                    $this->assertArrayHasKey('name', $item);
                    $this->assertNull($item['name']);
                }
            }
        } catch (Exception $e) {
            $exception = $e;
        }

        // emulate finally: always restore handler & error_reporting
        set_error_handler($previousHandler);
        error_reporting($previousErrorReporting);

        if ($exception !== null) {
            // rethrow after cleanup so the test still fails correctly
            throw $exception;
        }
    }

}
