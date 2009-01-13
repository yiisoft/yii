<?php $this->renderPartial('_post',array(
	'post'=>$post,
)); ?>

<div id="comments">
<?php if($post->commentCount>=1): ?>
<h3>
  <?php echo $post->commentCount>1 ? $post->commentCount . ' comments' : 'One comment'; ?>
  to "<?php echo CHtml::encode($post->title); ?>"
</h3>
<?php endif; ?>

<?php $this->renderPartial('/comment/_list',array(
	'comments'=>$comments,
	'post'=>$post,
)); ?>

<h3>Leave a Comment</h3>

<?php $this->renderPartial('/comment/_form',array(
	'comment'=>$newComment,
	'buttonLabel'=>'Submit',
)); ?>

</div><!-- comments -->
