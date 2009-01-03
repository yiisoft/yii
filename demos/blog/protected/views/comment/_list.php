<?php foreach($comments as $comment): ?>

<?php if(Yii::app()->user->isGuest && $comment->status==Comment::STATUS_PENDING) continue; ?>

<div class="comment" id="c<?php echo $comment->id; ?>">
  <?php echo CHtml::link("#{$comment->id}",array('post/show','id'=>isset($post)?$post->id:$comment->post->id,'#'=>$comment->id),array(
      'class'=>'cid',
      'title'=>'Permalink to this comment',
  )); ?>
  <div class="author"><?php echo $comment->authorLink; ?> says:</div>
  <div class="time">
    <?php if(!Yii::app()->user->isGuest): ?>
      <?php if($comment->status==Comment::STATUS_PENDING): ?>
        <span class="pending">Pending approval</span> |
        <?php echo CHtml::linkButton('Approve', array(
	        'submit'=>array('comment/approve','id'=>$comment->id),
	    )); ?> |
	  <?php endif; ?>
      <?php echo CHtml::link('Update',array('comment/update','id'=>$comment->id)); ?> |
      <?php echo CHtml::linkButton('Delete', array(
	        'submit'=>array('comment/delete','id'=>$comment->id),
            'confirm'=>"Are you sure to delete comment #{$comment->id}?",
      )); ?> |
    <?php endif; ?>
    <?php echo date('F j, Y \a\t h:i a',$comment->createTime); ?>
  </div>
  <div class="content"><?php echo $comment->contentDisplay; ?></div>
</div><!-- comment -->

<?php endforeach; ?>
