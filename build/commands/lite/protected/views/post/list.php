<h2>Post List</h2>

<div class="actionBar">
[<?php echo CHtml::link('New Post',array('create')); ?>]
</div>

<table class="dataGrid">
  <tr>
    <th><?php echo $this->generateColumnHeader('id'); ?></th>
    <th><?php echo $this->generateColumnHeader('title'); ?></th>
    <th><?php echo $this->generateColumnHeader('create_time'); ?></th>
    <th><?php echo $this->generateColumnHeader('author_id'); ?></th>
	<th>Actions</th>
  </tr>
<?php foreach($postList as $n=>$post): ?>
  <tr class="<?php echo $n%2?'even':'odd';?>">
    <td><?php echo CHtml::link($post->id,array('show','id'=>$post->id)); ?></td>
    <td><?php echo CHtml::encode($post->title); ?></td>
    <td><?php echo CHtml::encode($post->create_time); ?></td>
    <td><?php echo CHtml::encode($post->author_id); ?></td>
    <td>
      <?php echo CHtml::link('Update',array('update','id'=>$post->id)); ?>
      <?php echo CHtml::linkButton('Delete',array('submit'=>array('delete','id'=>$post->id),'confirm'=>'Are you sure?')); ?>
	</td>
  </tr>
<?php endforeach; ?>
</table>
