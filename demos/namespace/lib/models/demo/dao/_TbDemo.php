<?php

namespace lib\models\demo\dao;

/**
 * This is the model class for table "tb_demo".
 *
 * The followings are the available columns in table 'tb_demo':
 * @property integer $id
 * @property integer $num_a
 * @property integer $num_b
 * @property integer $num_c
 */
abstract class _TbDemo extends \lib\models\BaseActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'tb_demo';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('num_a, num_b, num_c', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, num_a, num_b, num_c', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'num_a' => 'Num A',
			'num_b' => 'Num B',
			'num_c' => 'Num C',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return \CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new \CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('num_a',$this->num_a);
		$criteria->compare('num_b',$this->num_b);
		$criteria->compare('num_c',$this->num_c);

		return new \CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

}
