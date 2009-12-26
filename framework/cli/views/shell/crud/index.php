<?php
/**
 * This is the template for generating the list view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<h2>List <?php echo $modelClass; ?></h2>

<ul class="actions">
	<li><?php echo "<?php echo CHtml::link('Create {$modelClass}',array('create')); ?>"; ?></li>
	<li><?php echo "<?php echo CHtml::link('Manage {$modelClass}',array('admin')); ?>"; ?></li>
</ul><!-- actions -->

<?php echo "<?php \$this->widget('CLinkPager',array('pages'=>\$pages)); ?>" ?>

<?php echo "<?php foreach(\$models as \$n=>\$model): ?>\n"; ?>
<div class="item">
<?php echo "<?php echo CHtml::encode(\$model->getAttributeLabel('{$ID}')); ?>"; ?>:
<?php echo "<?php echo CHtml::link(\$model->{$ID},array('view','id'=>\$model->{$ID})); ?>"; ?>

<br/>
<?php foreach($columns as $column): ?>
<?php echo "<?php echo CHtml::encode(\$model->getAttributeLabel('{$column->name}')); ?>"; ?>:
<?php echo "<?php echo CHtml::encode(\$model->{$column->name}); ?>"; ?>

<br/>
<?php endforeach; ?>

</div>
<?php echo "<?php endforeach; ?>\n"; ?>
<br/>
<?php echo "<?php \$this->widget('CLinkPager',array('pages'=>\$pages)); ?>" ?>
