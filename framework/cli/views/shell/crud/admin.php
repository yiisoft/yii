<?php
/**
 * This is the template for generating the admin view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<?php
echo "<?php\n";
$label=$this->class2name($modelClass,true);
echo "\$this->breadcrumbs=array(
	'$label'=>array('index'),
	'Manage',
);\n";
?>
?>
<h1>Manage <?php echo $this->class2name($modelClass,true); ?></h1>

<ul class="actions">
	<li><?php echo "<?php echo CHtml::link('List {$modelClass}',array('index')); ?>"; ?></li>
	<li><?php echo "<?php echo CHtml::link('Create {$modelClass}',array('create')); ?>"; ?></li>
</ul><!-- actions -->

<?php echo "<?php"; ?> $this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
	'columns'=>array(
<?php
$count=0;
foreach($columns as $column)
{
	if(++$count==7)
		echo "\t\t/*\n";
	echo "\t\t'".$column->name."',\n";
}
if($count>=7)
	echo "\t\t*/\n";
?>
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
