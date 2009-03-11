<?php

class Tag extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'Tag';
	}

	/**
	 * Returns tag names and their corresponding weights.
	 * Only the tags with the top weights will be returned.
	 * @param integer the maximum number of tags that should be returned
	 * @return array weights indexed by tag names
	 */
	public function findTagWeights($limit=20)
	{
		$criteria=new CDbCriteria(array(
			'select'=>'name, COUNT(postId) as weight',
			'join'=>'INNER JOIN PostTag ON Tag.id=PostTag.tagId',
			'group'=>'name',
			'having'=>'COUNT(postId)>0',
			'order'=>'weight DESC',
			'limit'=>20,
		));

		$rows=$this->dbConnection->commandBuilder->createFindCommand($this->tableSchema, $criteria)->queryAll();

		$total=0;
		foreach($rows as $row)
			$total+=$row['weight'];

		$tags=array();
		if($total>0)
		{
			foreach($rows as $row)
				$tags[$row['name']]=8+(int)(16*$row['weight']/($total+10));
			ksort($tags);
		}
		return $tags;
	}
}