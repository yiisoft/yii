<h2>Update Post <?php echo CHtml::link('#'.$post->id, array('post/show','id'=>$post->id)); ?></h2>

<?php $this->renderPartial('_form', array(
	'post'=>$post,
	'buttonLabel'=>'Save',
)); ?>
