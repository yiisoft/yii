<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="language" content="en" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl; ?>/css/main.css" />
<title>Login Required</title>

</head>

<body class="page">

<div id="container">
  <div id="header">
    <h1><?php echo CHtml::link(CHtml::encode(Yii::app()->params['title']),Yii::app()->homeUrl); ?></h1>
  </div><!-- header -->
  <div id="content">
    <h2>Login Required</h2>
    <p>
    You are not allowed to perform this action. Please login first.
    </p>
    <p>
    If you think this is a server error, please contact
    <?php echo CHtml::mailto(Yii::app()->params['adminEmail']); ?>.
    </p>
    <p>
    <?php echo CHtml::link('Return to homepage',Yii::app()->homeUrl); ?>
    </p>
  </div><!-- content -->

  <br class="clearfloat" />

  <div id="footer">
    <p><?php echo Yii::app()->params['copyrightInfo']; ?><br/>
    All Rights Reserved.<br/>
    <?php echo Yii::powered(); ?></p>
  </div><!-- footer -->
</div><!-- container -->
</body>

</html>