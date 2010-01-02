<?php
/**
 * This is the template for generating the 'view' view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<?php
echo "<?php\n";
$nameColumn=$this->guessNameColumn($columns);
$label=$this->class2name($modelClass,true);
echo "\$this->breadcrumbs=array(
	'$label'=>array('index'),
	\$model->{$nameColumn},
);\n";
?>
?>
<h1>View <?php echo $modelClass." #<?php echo \$model->{$ID}; ?>"; ?></h1>

<ul class="actions">
	<li><?php echo "<?php echo CHtml::link('List {$modelClass}',array('index')); ?>"; ?></li>
	<li><?php echo "<?php echo CHtml::link('Create {$modelClass}',array('create')); ?>"; ?></li>
	<li><?php echo "<?php echo CHtml::link('Update {$modelClass}',array('update','id'=>\$model->{$ID})); ?>"; ?></li>
	<li><?php echo "<?php echo CHtml::linkButton('Delete {$modelClass}',array('submit'=>array('delete','id'=>\$model->{$ID}),'confirm'=>'Are you sure to delete this item?')); ?>"; ?></li>
	<li><?php echo "<?php echo CHtml::link('Manage {$modelClass}',array('admin')); ?>"; ?></li>
</ul><!-- actions -->

<?php echo "<?php"; ?> $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
<?php
foreach($columns as $column)
	echo "\t\t'".$column->name."',\n";
?>
	),
)); ?>
