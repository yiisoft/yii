<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="language" content="en" />
<title><?php echo $this->pageTitle; ?></title>
</head>

<body>
<h1>Phone Book Demo</h1>
<p>
This demo shows how to use Yii to implement a Web service used by an Adobe Flex 3.0 client.
</p>

<p>
In order to see this demo, the PHP SOAP extension must be enabled and your browser should have
installed Adobe Flash Player version 9 or above.
</p>

<div>
<?php echo CHtml::link('View WSDL for this Web service',array('phonebook')); ?>
</div>

<div>
<?php if(extension_loaded('soap')): ?>
<?php $this->widget('CFlexWidget',array(
	'baseUrl'=>Yii::app()->baseUrl.'/flex/bin',
	'name'=>'phonebook',
	'width'=>'800',
	'height'=>'300',
	'align'=>'left',
	'flashVars'=>array(
		'wsdl'=>$this->createUrl('phonebook'),
	))); ?>
<?php else: ?>
Sorry, the PHP SOAP extension is not enabled on your Web server.
<?php endif; ?>
</div>


</body>

</html>