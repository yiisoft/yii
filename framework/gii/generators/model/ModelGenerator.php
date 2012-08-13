<?php

class ModelGenerator extends CCodeGenerator
{
	public $codeModel='gii.generators.model.ModelCode';

	/**
	 * Provides autocomplete table names
	 * @param string $db the database connection component id
	 * @param string $q the user entered term for filtering tablenames
	 * @return string the json array of tablenames that contains the entered term $q
	 */
	public function actionGetTableNames($db, $q)
	{
		if(Yii::app()->getRequest()->getIsAjaxRequest())
		{
			if(empty($db) || Yii::app()->hasComponent($db)===false || !(Yii::app()->getComponent($db) instanceof CDbConnection))
				throw new CHttpException(404,'The database component is not valid.');

			$model=$this->prepare();
			$all=array_keys(Yii::app()->{$db}->schema->getTables());

			if($q!=='')
			{
				$filtered=array();
				foreach($all as $table)
				{
					if(stripos($table, $q) !== false)
						$filtered[]=$table;
				}
				echo json_encode($filtered);
			}
			else
				echo json_encode($all);
		}
		else
			throw new CHttpException(404,'The requested page does not exist.');
	}
}