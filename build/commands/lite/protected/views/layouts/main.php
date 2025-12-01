<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="language" content="en" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
<title><?php echo $this->pageTitle; ?></title>
</head>

<body>
<div id="page">

<div id="header">
<div id="logo"><?php echo CHtml::encode(Yii::app()->name); ?></div>
<div id="mainmenu">
<?php $this->widget('application.components.MainMenu',array(
	'items'=>array(
		array('label'=>'Home', 'url'=>array('site/index')),
		array('label'=>'Login', 'url'=>array('site/login'), 'visible'=>Yii::app()->user->isGuest),
		array('label'=>'Logout', 'url'=>array('site/logout'), 'visible'=>!Yii::app()->user->isGuest)
	),
)); ?>
</div><!-- mainmenu -->
</div><!-- header -->

<div id="content">
<?php echo $content; ?>
</div><!-- content -->

<div id="footer">
Copyright &copy; 2009 by My Company.<br/>
All Rights Reserved.<br/>
</div><!-- footer -->

</div><!-- page -->
</body>

</html>