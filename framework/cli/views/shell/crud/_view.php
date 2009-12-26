<?php
/**
 * This is the template for generating the partial view for rendering a single model.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<div class="view">

<?php
foreach($columns as $i=>$column)
{
	if($i==6)
		echo "\t<?php /*\n";
	echo "\t<?php echo CHtml::encode(\$data->getAttributeLabel('{$column->name}')); ?>:\n";
	echo "\t<?php echo CHtml::encode(\$data->{$column->name}); ?>\n\t<br />\n\n";
}
if($i>=6)
	echo "\t*/ ?>\n";
?>

</div>