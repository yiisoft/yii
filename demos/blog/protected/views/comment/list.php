<h2>Comments Pending Approval</h2>

<h3><?php echo count($comments) . (count($comments)>1 ? ' comments':' comment'); ?> to approve</h3>

<?php $this->renderPartial('_list',array(
	'comments'=>$comments,
)); ?>

<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>