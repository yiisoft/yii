<?php
$cs=Yii::app()->clientScript;
$cs->coreScriptPosition=CClientScript::POS_HEAD;
$cs->scriptMap=array();
$baseUrl=$this->module->assetsUrl;
$cs->registerCoreScript('jquery');
$cs->registerScriptFile($baseUrl.'/js/tooltip.js');
$cs->registerScriptFile($baseUrl.'/js/fancybox/jquery.fancybox-1.3.1.pack.js');
$cs->registerCssFile($baseUrl.'/js/fancybox/jquery.fancybox-1.3.1.css');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo $this->module->assetsUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->module->assetsUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo $this->module->assetsUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo $this->module->assetsUrl; ?>/css/main.css" />

	<script type="text/javascript" src="<?php echo $this->module->assetsUrl; ?>/js/main.js"></script>

</head>

<body>

<div class="container" id="page">
	<header id="header">
		<div class="top-menus">
		<?php echo CHtml::link('help','http://www.yiiframework.com/doc/guide/topics.gii'); ?> |
		<?php echo CHtml::link('webapp',Yii::app()->homeUrl); ?> |
		<a href="http://www.yiiframework.com">yii</a>
		<?php if(!Yii::app()->user->isGuest): ?>
			| <?php echo CHtml::link('logout',array('default/logout')); ?>
		<?php endif; ?>
		</div>
		<div id="logo"><?php echo CHtml::link(CHtml::image($this->module->assetsUrl.'/images/logo.png'),array('default/index')); ?></div>
	</header><!-- header -->

	<?php echo $content; ?>

</div><!-- page -->

<footer id="footer">
	<?php echo Yii::powered(); ?>
	<br/>A product of <a href="http://www.yiisoft.com">Yii Software LLC</a>.
</footer><!-- footer -->

</body>
</html>