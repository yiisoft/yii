<!DOCTYPE html>
<html lang="en">
<head>
	<base href="<?=Yii::app()->baseUrl; ?>/">
	<meta charset="<?=Yii::app()->charset; ?>" />
	<meta name="language" content="en" />

	<!-- blueprint CSS framework -->
	<?=CHtml::cssFile('css/screen.css', 'screen, projection');?>	
	<?=CHtml::cssFile('css/print.css', 'print');?>	
	
	<!--[if lt IE 8]>
	<?=CHtml::cssFile('css/ie.css', 'screen, projection');?>	
	<![endif]-->

	<?=CHtml::cssFile('css/main.css');?>	
	<?=CHtml::cssFile('css/form.css');?>	

	<title><?=Yii::app()->name;?></title>
</head>

<body>

<div class="container" id="page">

	<div id="header">
		<div id="logo"><?=Yii::app()->name;?></div>
	</div><!-- header -->

	<div id="mainmenu">
		<?php $this->widget('zii.widgets.CMenu',array(
			'items'=>array(
				array('label'=>'Home', 'url'=>array('post/index')),
				array('label'=>'About', 'url'=>array('site/page', 'view'=>'about')),
				array('label'=>'Contact', 'url'=>array('site/contact')),
				array('label'=>'Login', 'url'=>array('site/login'), 'visible'=>Yii::app()->user->isGuest),
				array('label'=>'Logout ('.Yii::app()->user->name.')', 'url'=>array('site/logout'), 'visible'=>!Yii::app()->user->isGuest)
			),
		)); ?>
	</div><!-- mainmenu -->

	<?php $this->widget('zii.widgets.CBreadcrumbs', array(
		'links'=>$this->breadcrumbs,
	)); ?><!-- breadcrumbs -->

	<?=$content;?>

	<div id="footer">
		Copyright &copy; <?=date('Y');?> by My Company.<br/>
		All Rights Reserved.<br/>
		<?=Yii::powered();?>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>