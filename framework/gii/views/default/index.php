<h1>Welcome to Yee Code Generator!</h1>

<p>
	You may use the following generators to quickly build up your Yee application:
</p>
<ul>
	<?php foreach($this->module->controllerMap as $name=>$config): ?>
	<li><?php echo CHtml::link(ucwords(CHtml::encode($name).' generator'),array($name.'/index'));?></li>
	<?php endforeach; ?>
</ul>

