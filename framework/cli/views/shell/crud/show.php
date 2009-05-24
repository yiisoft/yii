<?php
/**
 * This is the template for generating the show view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $model: the finder object
 * - $modelClass: the model class name
 * - $modelVar: the PHP variable name storing the model instance
 * - $columns: a list of column schema objects
 */
?>
<h2>View <?php echo $modelClass." <?php echo \${$modelVar}->{$ID}; ?>"; ?></h2>

<div class="actionBar">
[<?php echo "<?php echo CHtml::link('{$modelClass} List',array('list')); ?>"; ?>]
[<?php echo "<?php echo CHtml::link('New {$modelClass}',array('create')); ?>"; ?>]
[<?php echo "<?php echo CHtml::link('Update {$modelClass}',array('update','id'=>\${$modelVar}->{$ID})); ?>"; ?>]
[<?php echo "<?php echo CHtml::linkButton('Delete {$modelClass}',array('submit'=>array('delete','id'=>\${$modelVar}->{$ID}),'confirm'=>'Are you sure?')); ?>\n"; ?>]
[<?php echo "<?php echo CHtml::link('Manage {$modelClass}',array('admin')); ?>"; ?>]
</div>

<table class="dataGrid">
<?php foreach($columns as $name=>$column): ?>
<tr>
	<th class="label"><?php echo "<?php echo CHtml::encode(\${$modelVar}->getAttributeLabel('$name')); ?>\n"; ?></th>
    <td><?php echo "<?php echo CHtml::encode(\${$modelVar}->{$name}); ?>\n"; ?></td>
</tr>
<?php endforeach; ?>
</table>
