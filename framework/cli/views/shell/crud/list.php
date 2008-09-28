<h2><?php echo $modelClass; ?> List</h2>

<div class="actionBar">
[<?php echo "<?php echo CHtml::link('New {$modelClass}',array('create')); ?>"; ?>]
</div>

<table class="dataGrid">
  <tr>
    <th><?php echo "<?php echo \$this->generateColumnHeader('$ID'); ?>"; ?></th>
<?php foreach($columns as $column): ?>
    <th><?php echo "<?php echo \$this->generateColumnHeader('{$column->name}'); ?>"; ?></th>
<?php endforeach; ?>
	<th>Actions</th>
  </tr>
<?php echo "<?php foreach(\${$modelVar}List as \$n=>\${$modelVar}): ?>\n"; ?>
  <tr class="<?php echo "<?php echo \$n%2?'even':'odd';?>"; ?>">
    <td><?php echo "<?php echo CHtml::link(\${$modelVar}->{$ID},array('show','id'=>\${$modelVar}->{$ID})); ?>"; ?></td>
<?php foreach($columns as $column): ?>
    <td><?php echo "<?php echo CHtml::encode(\${$modelVar}->{$column->name}); ?>"; ?></td>
<?php endforeach; ?>
    <td>
      <?php echo "<?php echo CHtml::link('Update',array('update','id'=>\${$modelVar}->{$ID})); ?>\n"; ?>
      <?php echo "<?php echo CHtml::linkButton('Delete',array('submit'=>array('delete','id'=>\${$modelVar}->{$ID}),'confirm'=>'Are you sure?')); ?>\n"; ?>
	</td>
  </tr>
<?php echo "<?php endforeach; ?>\n"; ?>
</table>

<?php echo "<?php \$this->widget('CLinkPager',array('pages'=>\$pages)); ?>" ?>
