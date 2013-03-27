<?php
/**
 * CCubridCommandBuilder class file.
 *
 * @author Esen Sagynov <kadishmal@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCubridCommandBuilder provides basic methods to create query commands for tables for CUBRID database servers.
 *
 * @author Esen Sagynov <kadishmal@gmail.com>
 * @package system.db.schema.cubrid
 * @since 1.1.14
 */
class CCubridCommandBuilder extends CDbCommandBuilder
{
	/**
	 * @var array CUBRID operator special characters
	 */
	private $operatorSpecialChars = array('+', '-', '*', '/', '%', '|', '!', '<', '>', '=', '^', '&' , '~');

	/**
	 * Creates a SELECT command for a single table.
	 * @param CDbTableSchema $table the table metadata
	 * @param CDbCriteria $criteria the query criteria
	 * @param string $alias the alias name of the primary table. Defaults to 't'.
	 * @return CDbCommand query command.
	 */
	public function createFindCommand($table,$criteria,$alias='t')
	{
		$columns=$table->getColumnNames();

		$select=is_array($criteria->select) ? implode(', ',$criteria->select) : $criteria->select;

		$criteria->select=$this->quoteColumnNames($columns,$select);
		$criteria->condition=$this->quoteColumnNames($columns,$criteria->condition);
		$criteria->order=$this->quoteColumnNames($columns,$criteria->order);
		$criteria->group=$this->quoteColumnNames($columns,$criteria->group);
		$criteria->having=$this->quoteColumnNames($columns,$criteria->having);

		return parent::createFindCommand($table,$criteria,$alias);
	}

	/**
	 * Creates a COUNT(*) command for a single table.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param CDbCriteria $criteria the query criteria
	 * @param string $alias the alias name of the primary table. Defaults to 't'.
	 * @return CDbCommand query command.
	 */
	public function createCountCommand($table,$criteria,$alias='t')
	{
		$columns=$table->getColumnNames();

		$select=is_array($criteria->select) ? implode(', ',$criteria->select) : $criteria->select;

		$criteria->select=$this->quoteColumnNames($columns,$select);
		$criteria->condition=$this->quoteColumnNames($columns,$criteria->condition);
		$criteria->order=$this->quoteColumnNames($columns,$criteria->order);
		$criteria->group=$this->quoteColumnNames($columns,$criteria->group);
		$criteria->having=$this->quoteColumnNames($columns,$criteria->having);

		return parent::createCountCommand($table,$criteria,$alias);
	}

	/**
	 * Creates a DELETE command.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param CDbCriteria $criteria the query criteria
	 * @return CDbCommand delete command.
	 */
	public function createDeleteCommand($table,$criteria)
	{
		$columns=$table->getColumnNames();
		// DELETE FROM query has only WHERE condition, no ORDER BY,
		// so quote only WHERE condition.
		$criteria->condition=$this->quoteColumnNames($columns,$criteria->condition);

		return parent::createDeleteCommand($table,$criteria);
	}

	/**
	 * Creates an UPDATE command.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $data list of columns to be updated (name=>value)
	 * @param CDbCriteria $criteria the query criteria
	 * @return CDbCommand update command.
	 */
	public function createUpdateCommand($table,$data,$criteria)
	{
		$columns=$table->getColumnNames();

		$criteria->condition=$this->quoteColumnNames($columns,$criteria->condition);
		$criteria->order=$this->quoteColumnNames($columns,$criteria->order);
		$criteria->group=$this->quoteColumnNames($columns,$criteria->group);
		$criteria->having=$this->quoteColumnNames($columns,$criteria->having);

		return parent::createUpdateCommand($table,$data,$criteria);
	}

	/**
	 * Creates an UPDATE command that increments/decrements certain columns.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $counters counters to be updated (counter increments/decrements indexed by column names.)
	 * @param CDbCriteria $criteria the query criteria
	 * @return CDbCommand the created command
	 * @throws CException if no counter is specified
	 */
	public function createUpdateCounterCommand($table,$counters,$criteria)
	{
		$columns=$table->getColumnNames();

		$criteria->condition=$this->quoteColumnNames($columns,$criteria->condition);
		$criteria->order=$this->quoteColumnNames($columns,$criteria->order);
		$criteria->group=$this->quoteColumnNames($columns,$criteria->group);
		$criteria->having=$this->quoteColumnNames($columns,$criteria->having);

		return parent::createUpdateCounterCommand($table,$counters,$criteria);
	}

	private function quoteColumnNames($columns, $expression)
	{
		if (strlen($expression) > 0)
		{
			$pos = 0;
			$newExpression = '';

			// search for the beginning of a character string which is
			// enclosed in single quotes in CUBRID. We will not parse them,
			// so will skip.
			while (($posEnd = stripos($expression, "'")) !== false)
			{
				$newExpression .= $this->quoteColumnNamesInRange($columns, substr($expression, $pos, $posEnd - $pos));
				// find the closing quote. It MUST be present, otherwise this is
				// user's mistake.
				$pos = stripos($expression, "'", $posEnd + 1);
				// If two consequtive single quotes found, the first one
				// escapes the second. So, we have to look further.
				while ($pos !== false && substr($expression, $posEnd + 1, 1) == "'")
				{
					$pos = strpos($expression, "'", $pos + 1);
				}
				// add back the quoted character string itself
				$newExpression .= substr($expression, $posEnd, $pos - $posEnd + 1);
				// now parse the rest part
				$expression = substr($expression, $pos + 1);
			}
			// parse the rest part
			$newExpression .= $this->quoteColumnNamesInRange($columns, $expression);
			$expression = $newExpression;
		}

		return $expression;
	}

	private function quoteColumnNamesInRange($columns, $expression)
	{
		if (strlen($expression) > 0)
		{
			// search for the beginning of a identifier wrapper (`) which
			// wraps the column names.
			$posEnd = stripos($expression, '`');

			if ($posEnd === false)
			{
				foreach ($columns as $column)
				{
					$pos = $posEnd = 0;
					$len = strlen($column);

					while (($posEnd = stripos($expression, $column, $pos)) !== false)
					{
						if ($posEnd > 0)
						{
							$prevChar = substr($expression, $posEnd - 1, 1);
						}
						else{
							$prevChar = false;
						}
						$nextChar = substr($expression, $posEnd + $len, 1);
						// 1. Check if it is not a placeholder, i.e. if the
						// previous character is not a collon.
						// 2. Also we have to make sure it's not a part of another
						// column name, eg. "type" in "category_type". In this
						// case which have to look further.
						// 3. We also need to ignore space and a comma delimeter
						// between columns.
						// 4. If there is no any character before/after (false)
						// also skip.
						if ($prevChar !== ':' && 
							(in_array($prevChar, $this->operatorSpecialChars) OR
								$prevChar == ' ' OR $prevChar == ',' OR $prevChar == '(' OR $prevChar === false
							) &&
							(in_array($nextChar, $this->operatorSpecialChars) OR
								$nextChar == ' ' OR $nextChar == ',' OR $nextChar === false
							))
						{
							$expression = substr($expression, 0, $posEnd) .
									$this->schema->quoteSimpleColumnName($column) .
									substr($expression, $posEnd + $len);

							// 2 for one openning quote and one closing quote
							$posEnd += 2;
						}
						// move further beyond this column name
						$pos = $posEnd + $len;
					}
				}
			}
			// If the column name has already been quoted, then we just skip it.
			else{
				$newExpression = $this->quoteColumnNamesInRange($columns, substr($expression, 0, $posEnd));
				// find the closing quote. It MUST be present, otherwise this is
				// user's mistake.
				$pos = stripos($expression, '`', $posEnd + 1);
				// add back the quoted column name itself
				$newExpression .= substr($expression, $posEnd, $pos - $posEnd + 1);
				// now parse the rest part
				$newExpression .= $this->quoteColumnNamesInRange($columns, substr($expression, $pos + 1));
				$expression = $newExpression;
			}
		}

		return $expression;
	}
}
