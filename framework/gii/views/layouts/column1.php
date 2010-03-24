<?php $this->beginContent('gii.views.layouts.main'); ?>
<div class="container">
	<?php $this->widget('zii.widgets.CBreadcrumbs', array(
		'links'=>$this->breadcrumbs,
		'homeLink'=>CHtml::link('Home',Yii::app()->createUrl('gii')),
	)); ?><!-- breadcrumbs -->

	<div id="content">
		<?php echo $content; ?>
	</div><!-- content -->
</div>
<?php $this->endContent(); ?>