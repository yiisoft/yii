<?php
/**
 * This is the template for generating the create view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<h2>New <?php echo $modelClass; ?></h2>

<div class="actionBar">
[<?php echo "<?php echo CHtml::link('{$modelClass} List',array('list')); ?>"; ?>]
[<?php echo "<?php echo CHtml::link('Manage {$modelClass}',array('admin')); ?>"; ?>]
</div>

<?php echo "<?php echo \$this->renderPartial('_form', array(
	'model'=>\$model,
	'update'=>false,
)); ?>"; ?>
