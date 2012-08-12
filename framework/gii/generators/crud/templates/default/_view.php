<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php echo "<?php\n"; ?>
/* @var $this <?php echo $this->getControllerClass(); ?> */
/* @var $model <?php echo $this->getModelClass(); ?> */
?>

<div class="view">

<?php
if(is_string($this->tableSchema->primaryKey)){
	echo "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('{$this->tableSchema->primaryKey}')); ?>:</b>\n";
	echo "\t<?php echo CHtml::link(CHtml::encode(\$data->{$this->tableSchema->primaryKey}), array('view', 'id'=>\$data->{$this->tableSchema->primaryKey})); ?>\n\t<br />\n\n";
}
else if(is_array($this->tableSchema->primaryKey)){
	/* for composite primary keys, id is separated by the "|" character */
	$strPrimaryKeys = $strHtmlFields = '';
	
	foreach($this->tableSchema->primaryKey as $name){
		$strHtmlFields .= "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('{$name}')); ?>:</b>\n";
		$strHtmlFields .= "\t<?php echo CHtml::link(CHtml::encode(\$data->{$name}), array('view', 'id'=>idStringToReplaceLink)); ?>\n\t<br />\n\n";
		$strPrimaryKeys .= "\$data->{$name}.'|'.";
	}
	$strPrimaryKeys = substr($strPrimaryKeys, 0, -5);
	echo str_replace('idStringToReplaceLink', $strPrimaryKeys, $strHtmlFields);
}

$count=0;
foreach($this->tableSchema->columns as $column)
{
	if($column->isPrimaryKey)
		continue;
	if(++$count==7)
		echo "\t<?php /*\n";
	echo "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('{$column->name}')); ?>:</b>\n";
	echo "\t<?php echo CHtml::encode(\$data->{$column->name}); ?>\n\t<br />\n\n";
}
if($count>=7)
	echo "\t*/ ?>\n";
?>

</div>