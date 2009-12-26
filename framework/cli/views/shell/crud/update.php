<?php
/**
 * This is the template for generating the update view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<h2>Update <?php echo $modelClass." <?php echo \$model->{$ID}; ?>"; ?></h2>

<ul class="actions">
	<li><?php echo "<?php echo CHtml::link('List {$modelClass}',array('index')); ?>"; ?></li>
	<li><?php echo "<?php echo CHtml::link('Create {$modelClass}',array('create')); ?>"; ?></li>
	<li><?php echo "<?php echo CHtml::link('Manage {$modelClass}',array('admin')); ?>"; ?></li>
</ul><!-- actions -->

<?php echo "<?php echo \$this->renderPartial('_form', array('model'=>\$model)); ?>"; ?>