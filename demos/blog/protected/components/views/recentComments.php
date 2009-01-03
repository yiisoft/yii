<ul>
<?php foreach($this->getRecentComments() as $comment): ?>

<li><?php echo $comment->authorLink; ?> on
	<?php echo CHtml::link(CHtml::encode($comment->post->title),array('post/show','id'=>$comment->post->id)); ?>
</li>
<?php endforeach; ?>
</ul>