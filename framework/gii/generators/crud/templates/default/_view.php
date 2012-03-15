<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<div class="view">

<?php
$firstPkColumn=(array)$this->tableSchema->primaryKey;
$firstPkColumn=$firstPkColumn[0];
echo "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('$firstPkColumn')); ?>:</b>\n";
echo "\t<?php echo CHtml::link(CHtml::encode(\$data->$firstPkColumn), array('view', 'id'=>".$this->generatePrimaryKeyParam('$data').")); ?>\n\t<br />\n\n";
$count=0;
foreach($this->tableSchema->columns as $column)
{
	if($column->name===$firstPkColumn)
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
