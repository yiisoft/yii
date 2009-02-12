<h2>New <?php echo $modelClass; ?></h2>

<div class="actionBar">
[<?php echo "<?php echo CHtml::link('{$modelClass} List',array('list')); ?>"; ?>]
[<?php echo "<?php echo CHtml::link('Manage {$modelClass}',array('admin')); ?>"; ?>]
</div>

<?php echo "<?php echo \$this->renderPartial('_form', array(
	'$modelVar'=>\$$modelVar,
	'update'=>false,
)); ?>"; ?>
