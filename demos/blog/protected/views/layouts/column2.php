<?php $this->beginContent('/layouts/main'); ?>
<div class="container">
	<div class="span-18">
		<div id="content">
			<?php echo $content; ?>
		</div><!-- content -->
	</div>
	<div class="span-6 last">
		<div id="sidebar">
			<?php if(!Yee::app()->user->isGuest) $this->widget('UserMenu'); ?>

			<?php $this->widget('TagCloud', array(
				'maxTags'=>Yee::app()->params['tagCloudCount'],
			)); ?>

			<?php $this->widget('RecentComments', array(
				'maxComments'=>Yee::app()->params['recentCommentCount'],
			)); ?>
		</div><!-- sidebar -->
	</div>
</div>
<?php $this->endContent(); ?>