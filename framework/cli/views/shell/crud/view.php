<?php
/**
 * This is the template for generating the show view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<h2>View <?php echo $modelClass." <?php echo \$model->{$ID}; ?>"; ?></h2>

<ul class="actions">
	<li><?php echo "<?php echo CHtml::link('List {$modelClass}',array('index')); ?>"; ?></li>
	<li><?php echo "<?php echo CHtml::link('Create {$modelClass}',array('create')); ?>"; ?></li>
	<li><?php echo "<?php echo CHtml::link('Update {$modelClass}',array('update','id'=>\$model->{$ID})); ?>"; ?></li>
	<li><?php echo "<?php echo CHtml::linkButton('Delete {$modelClass}',array('submit'=>array('delete','id'=>\$model->{$ID}),'confirm'=>'Are you sure?')); ?>\n"; ?></li>
	<li><?php echo "<?php echo CHtml::link('Manage {$modelClass}',array('admin')); ?>"; ?></li>
</ul><!-- actions -->

<table class="dataGrid">
<?php foreach($columns as $name=>$column): ?>
<tr>
	<th class="label"><?php echo "<?php echo CHtml::encode(\$model->getAttributeLabel('$name')); ?>\n"; ?></th>
    <td><?php echo "<?php echo CHtml::encode(\$model->{$name}); ?>\n"; ?></td>
</tr>
<?php endforeach; ?>
</table>
