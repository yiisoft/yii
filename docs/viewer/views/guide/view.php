<div id="header">
	<h1>The Definitive Guide to Yii</h1>
	<?php echo CHtml::link('The Yii Blog Tutorial', array('blog/view'))?>
</div>

<div id="sidebar">
	<ul class="toc">
	<?php
	foreach($this->getTopics() as $title=>$topics)
	{
		echo '<li><b>'.$title."</b>\n\t<ul>\n";
		foreach($topics as $path=>$text)
		{
			if($path===$this->topic)
				echo "\t<li class=\"active\">";
			else
				echo "\t<li>";
			echo CHtml::link(CHtml::encode($text),array('view','page'=>$path,'lang'=>$this->language));
			echo "</li>\n";
		}
		echo "\t</ul>\n</li>\n";
	}
	?>
	</ul>
</div>

<div id="content">
	<div id="languages">
	<?php
	$links=array();
	foreach($this->getLanguages() as $id=>$language)
	{
		if($this->language===$id)
			$links[]=CHtml::link($language, array('view', 'page'=>$this->topic, 'lang'=>$id), array('class'=>'active'));
		else
			$links[]=CHtml::link($language, array('view', 'page'=>$this->topic, 'lang'=>$id));
	}
	echo implode(' | ',$links);
	?>
	</div>

	<?php echo $content?>
</div>

