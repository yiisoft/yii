<?php
/* @var $this SiteController */

$this->pageTitle=Yee::app()->name;
?>

<h1>Welcome to <i><?php echo CHtml::encode(Yee::app()->name); ?></i></h1>

<p>Congratulations! You have successfully created your Yee application.</p>

<p>You may change the content of this page by modifying the following two files:</p>
<ul>
	<li>View file: <code><?php echo __FILE__; ?></code></li>
	<li>Layout file: <code><?php echo $this->getLayoutFile('main'); ?></code></li>
</ul>

<p>For more details on how to further develop this application, please read
the <a href="http://www.yeeframework.com/doc/">documentation</a>.
Feel free to ask in the <a href="http://www.yeeframework.com/forum/">forum</a>,
should you have any questions.</p>
