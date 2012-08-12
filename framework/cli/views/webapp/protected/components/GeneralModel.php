<?php
class GeneralModel extends CActiveRecord
{	
	// This method allows you assign next values to table's primary key without worry for if 
	// is it a compound key or a simple key. This happends before insert data into table
	// This benefit was made for the most popular databases and saves you create code for that purpose,
	// The only thing you have to do for use it is extend your model's class from this class (GeneralModel).
	// If you have to do something different in "beforeSave" method, you can rewrite this method in your model's class for do what you want.
	public function beforeSave(){
		
		if ($this->getIsNewRecord()){
			$table = $this->getMetaData()->tableSchema;
			$arrayDBConnection = explode(':',Yii::app()->db->connectionString);
			// $dbType variable stores the database type, its value can be... 
			// oci => it means Oracle database
			// pgsql => it means PostgreSQL database
			// mysql => it means MySQL database
			// mssql => it means SQL Server database
			// sqlite => it means SQLite database
			$dbType = strtolower($arrayDBConnection[0]);
						
			// If the primary key is a simple key
			if(is_string($table->primaryKey)){
								
				//if the property has a value, we do nothing
				if ($this->{$table->primaryKey})
					return true;
				
				// The switch statement is used for compatibility with most popular database engines
				switch ($dbType) {
					case 'oci':
						$sqlSentence = 'select nvl(max('.$table->primaryKey.'),0)+1 from '.$this->tableName();
						break;
					
					case 'mysql':
						$sqlSentence = 'select IFNULL(MAX('.$table->primaryKey.'),0)+1 from '.$this->tableName();
						break;

					case 'pgsql':
						$sqlSentence = 'select coalesce(max('.$table->primaryKey.'),0)+1 from '.$this->tableName();
						break;
						
					case 'mssql':
						$sqlSentence = 'select ISNULL(MAX('.$table->primaryKey.'),0)+1 from '.$this->tableName();
						break;
						
					case 'sqlite':
						$sqlSentence = 'select ifnull(max('.$table->primaryKey.'),0)+1 from '.$this->tableName();
						break;
						
					default:
						$sqlSentence = '';
						break;
				}
				
				$command = Yii::app()->db->createCommand($sqlSentence);
				// The next line returns the value of the first column in the first row of data.
				$result = $command->queryScalar();
				// If we got a result from the query 
				if ($result)
					$this->{$table->primaryKey} = $result;
			}
			// If the primary key is a compound key	
			else if(is_array($table->primaryKey)){
				// Every item of the compound key is evaluated to know if is a foreign key, if not,  
				// the query returns data with the next value for assign it to the field, when this is numeric.
				$select = 'select';
				$where = '1 = 1';
				foreach($table->primaryKey as $nameField){
					//if is it a foreign key
					if ($table->getColumn($nameField)->isForeignKey){
						//if the field is not null
						if ($this->{$nameField}){
							$where .= ' AND '.$nameField.' = '.$this->{$nameField};
						}else{
							$this->addError($nameField,'Por favor ingrese un valor para el campo '.$nameField);
							return false;
						}
					//If is it a numeric field (integer or float)
					}else if(strcmp($table->getColumn($nameField)->type, 'integer') == 0 || strcmp($table->getColumn($nameField)->type, 'float') == 0){
						
						// The switch statement is used for compatibility with most popular database engines
						switch ($dbType) {
							case 'oci':
								$select .= ' nvl(max('.$nameField.'),0)+1 as '.$nameField.', ';
								break;
									
							case 'mysql':
								$select .= ' IFNULL(MAX('.$nameField.'),0)+1 as '.$nameField.', ';
								break;
						
							case 'pgsql':
								$select .= ' coalesce(max('.$nameField.'),0)+1 as '.$nameField.', ';
								break;
						
							case 'mssql':
								$select .= ' ISNULL(MAX('.$nameField.'),0)+1 as '.$nameField.', ';
								break;
						
							case 'sqlite':
								$select .= ' ifnull(max('.$nameField.'),0)+1 as '.$nameField.', ';
								break;
						
							default:
								$sqlSentence = '';
								break;
						}						
						
					//If is it a string field and the value is null, we not sugguest the next value, i mean, we request to user fill the field
					}else if(strcmp($table->getColumn($nameField)->type, 'string') == 0 && $this->{$nameField} == null){
						$this->addError($nameField,'Please insert a value for field'.$nameField);
						return false;
					}
				}
			
				// If the query was builded
				if (strcmp($select, 'select') != 0){
					
					$select = substr($select, 0, -2);
					$sqlSentence = $select.' from '.$this->tableName().' where '.$where;
					$command = Yii::app()->db->createCommand($sqlSentence);
					// The next line returns the value of the first row of data
					$result = $command->queryRow();
					// If we got a result from the query 
					if (is_array($result)){
						// we get the associative indexes from the first row of data returned
						$arrayQueryKeys = array_keys($result);
						// we assign the values for each property of the object to be inserted in database
						foreach ($arrayQueryKeys as $column){
							$this->{$column} = $result[$column];
						}
					}
				}
			}
		}
		return true;
	}
	
	
	// This method allows you list data from a model in order to create a HMTL select item (drop down list) in the form file.
	// This means helpers when you want to create or update a row in a transactional table who has fields that depends from other tables.
	// You can call this method from your _form file and deliver it the model's name, the id and description fields who you want get for
	// list in the html select item  
	// for Example: echo CHtml::activeDropDownList($model,'BANK_ID', $model->getListDataForeingModel('BANKS', 'BANK_ID', 'BANK_NAME'),array('prompt' => '(Select an option)')); 
	public function getListDataForeingModel($modelName, $idName, $descriptionName){
		// CHtml::listData method returns the data list from the model, id and description's names send by parameter 
		return CHtml::listData($modelName::model()->findAll(), $idName, $descriptionName);
	}
	
}