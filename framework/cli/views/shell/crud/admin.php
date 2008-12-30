<h2>Managing <?php echo $modelClass; ?></h2>

<div class="actionBar">
[<?php echo "<?php echo CHtml::link('{$modelClass} List',array('list')); ?>"; ?>]
[<?php echo "<?php echo CHtml::link('New {$modelClass}',array('create')); ?>"; ?>]
</div>

<table class="dataGrid">
  <tr>
    <th><?php echo "<?php echo \$sort->link('$ID'); ?>"; ?></th>
<?php foreach($columns as $column): ?>
    <th><?php echo "<?php echo \$sort->link('{$column->name}'); ?>"; ?></th>
<?php endforeach; ?>
	<th>Actions</th>
  </tr>
<?php echo "<?php foreach(\${$modelVar}List as \$n=>\$model): ?>\n"; ?>
  <tr class="<?php echo "<?php echo \$n%2?'even':'odd';?>"; ?>">
    <td><?php echo "<?php echo CHtml::link(\$model->{$ID},array('show','id'=>\$model->{$ID})); ?>"; ?></td>
<?php foreach($columns as $column): ?>
    <td><?php echo "<?php echo CHtml::encode(\$model->{$column->name}); ?>"; ?></td>
<?php endforeach; ?>
    <td>
      <?php echo "<?php echo CHtml::link('Update',array('update','id'=>\$model->{$ID})); ?>\n"; ?>
      <?php echo "<?php echo CHtml::linkButton('Delete',array(
      	  'submit'=>'',
      	  'params'=>array('command'=>'delete','id'=>\$model->{$ID}),
      	  'confirm'=>\"Are you sure to delete #{\$model->{$ID}}?\")); ?>\n"; ?>
	</td>
  </tr>
<?php echo "<?php endforeach; ?>\n"; ?>
</table>
<br/>
<?php echo "<?php \$this->widget('CLinkPager',array('pages'=>\$pages)); ?>" ?>
