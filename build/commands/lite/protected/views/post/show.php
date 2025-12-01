<h2>View Post <?php echo $post->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('Post List',array('list')); ?>]
[<?php echo CHtml::link('New Post',array('create')); ?>]
[<?php echo CHtml::link('Update Post',array('update','id'=>$post->id)); ?>]
[<?php echo CHtml::linkButton('Delete Post',array('submit'=>array('delete','id'=>$post->id),'confirm'=>'Are you sure?')); ?>
]
</div>

<table class="dataGrid">
<tr>
	<th class="label"><?php echo CHtml::encode($post->getAttributeLabel('title')); ?>
</th>
    <td><?php echo CHtml::encode($post->title); ?>
</td>
    </div>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($post->getAttributeLabel('create_time')); ?>
</th>
    <td><?php echo CHtml::encode($post->create_time); ?>
</td>
    </div>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($post->getAttributeLabel('author_id')); ?>
</th>
    <td><?php echo CHtml::encode($post->author_id); ?>
</td>
    </div>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($post->getAttributeLabel('content')); ?>
</th>
    <td><?php echo CHtml::encode($post->content); ?>
</td>
    </div>
</tr>
</table>
