<h2>Update Comment <?php echo CHtml::link("#{$comment->id}",array('post/show','id'=>$comment->post->id,'#'=>'c'.$comment->id)); ?></h2>

<?php $this->renderPartial('_form', array(
	'comment'=>$comment,
	'update'=>true,
)); ?>
