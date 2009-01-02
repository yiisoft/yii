<h2><?php echo $modelClass; ?> List</h2>

<div class="actionBar">
[<?php echo "<?php echo CHtml::link('New {$modelClass}',array('create')); ?>"; ?>]
[<?php echo "<?php echo CHtml::link('Manage {$modelClass}',array('admin')); ?>"; ?>]
</div>

<?php echo "<?php \$this->widget('CLinkPager',array('pages'=>\$pages)); ?>" ?>


<?php echo "<?php foreach(\${$modelVar}List as \$n=>\$model): ?>\n"; ?>
<div class="item">
<?php echo "<?php echo CHtml::encode(\$model->getAttributeLabel('{$ID}')); ?>"; ?>:
<?php echo "<?php echo CHtml::link(\$model->{$ID},array('show','id'=>\$model->{$ID})); ?>"; ?>

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
