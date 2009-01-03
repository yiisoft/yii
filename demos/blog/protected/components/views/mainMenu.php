<ul>
<li><?php echo CHtml::link('Home',Yii::app()->homeUrl); ?></li>
<li><?php echo CHtml::link('About',array('site/contact')); ?></li>
<?php if(Yii::app()->user->isGuest): ?>
<li><?php echo CHtml::link('Login',array('site/login')); ?></li>
<?php else: ?>
<li><?php echo CHtml::link('Create New Post',array('post/create')); ?></li>
<li><?php echo CHtml::link('Manage Posts',array('post/admin')); ?></li>
<li><?php echo CHtml::link('Approve Comments',array('comment/list')) . ' (' . Comment::model()->pendingCommentCount . ')'; ?></li>
<li><?php echo CHtml::link('Logout',array('site/logout')); ?></li>
<?php endif; ?>
</ul>