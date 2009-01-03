<?php if(!empty($_GET['tag'])): ?>
<h3>Posts Tagged with "<?php echo CHtml::encode($_GET['tag']); ?>"</h3>
<?php endif; ?>

<?php foreach($posts as $post): ?>
<?php $this->renderPartial('_post',array(
	'post'=>$post,
)); ?>
<?php endforeach; ?>

<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>