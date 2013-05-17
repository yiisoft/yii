<?php

class GuideController extends CController
{
	public $defaultAction='view';
	public $languageNames=array(
		'de'=>'German',
		'en'=>'English',
		'es'=>'Spanish',
		'fr'=>'French',
		'he'=>'Hebrew',
		'hu'=>'Hungarian',
		'id'=>'Indonesian',
		'it'=>'Italian',
		'nl'=>'Dutch',
		'no'=>'Norwegian',
		'ja'=>'Japanese',
		'pl'=>'Polish',
		'pt'=>'Portuguese',
		'pt_br'=>'Portuguese (Brazilian)',
		'ru'=>'Russian',
		'sv'=>'Swedish',
		'ta_in'=>'Tamil',
		'vi'=>'Vietnamese',
		'uk' => 'Ukrainian',
		'zh_cn'=>'Simplified Chinese',
		'zh_tw'=>'Traditional Chinese',
	);
	private $_languages;
	private $_language;
	private $_topics;

	public function actionView()
	{
		$topic=$this->getTopic();
		$file=Yii::getPathOfAlias("docs.guide.{$this->language}").DIRECTORY_SEPARATOR.$topic.'.txt';

		if(!is_file($file))
			$file=Yii::getPathOfAlias("docs.guide").DIRECTORY_SEPARATOR.$topic.'.txt';

		if(!strcasecmp($topic,'toc') || !is_file($file))
			throw new CHttpException(404,'The page you looked for does not exist.');

		$content=file_get_contents($file);
		$markdown=new MarkdownParser;
		$content=$markdown->safeTransform($content);

		$imageUrl=Yii::app()->baseUrl.'/guide/images';
		$content=preg_replace('/<p>\s*<img(.*?)src="(.*?)"\s+alt="(.*?)"\s*\/>\s*<\/p>/',
			"<div class=\"image\"><p>\\3</p><img\\1src=\"$imageUrl/\\2\" alt=\"\\3\" /></div>",$content);

		$content=preg_replace_callback('/href="\/doc\/guide\/(.*?)\/?"/',array($this,'replaceGuideLink'),$content);
		$content=preg_replace('/href="(\/doc\/api\/.*?)"/','href="http://www.yiiframework.com$1"',$content);

		$this->pageTitle='The Definitive Guide to Yii';
		if($topic!=='index' && preg_match('/<h1[^>]*>(.*?)</',$content,$matches))
			$this->pageTitle.=' - '.CHtml::encode($matches[1]);

		$this->render('view',array('content'=>$content));
	}

	public function replaceGuideLink($matches)
	{
		if(($pos=strpos($matches[1],'#'))!==false)
		{
			$anchor=substr($matches[1],$pos);
			$matches[1]=substr($matches[1],0,$pos);
		}
		else
			$anchor='';
		return 'href="'.$this->createUrl('view',array('lang'=>$this->language,'page'=>$matches[1])).$anchor.'"';
	}

	public function getTopic()
	{
		if(!isset($_GET['page']) || empty($_GET['page']))
			return 'index';
		else
			return str_replace(array('/','\\'),'',trim($_GET['page']));
	}

	public function getTopics()
	{
		if($this->_topics===null)
		{
			$file=Yii::getPathOfAlias("docs.guide.{$this->language}.toc").'.txt';
			if(!is_file($file))
				$file=Yii::getPathOfAlias('docs.guide.toc').'.txt';
			$lines=file($file);
			$chapter='';
			foreach($lines as $line)
			{
				if(($line=trim($line))==='')
					continue;
				if($line[0]==='*')
					$chapter=trim($line,'* ');
				else if($line[0]==='-' && preg_match('/\[(.*?)\]\((.*?)\)/',$line,$matches))
					$this->_topics[$chapter][$matches[2]]=$matches[1];
			}
		}
		return $this->_topics;
	}

	public function getLanguage()
	{
		if($this->_language===null)
		{
			if(isset($_GET['lang']) && preg_match('/^[a-z_]+$/',$_GET['lang']))
				$this->_language=$_GET['lang'];
			else
				$this->_language='en';
		}
		return $this->_language;
	}

	public function getLanguages()
	{
		if($this->_languages===null)
		{
			$basePath=Yii::getPathOfAlias('docs.guide');
			$dir=opendir($basePath);
			$this->_languages=array('en'=>'English');
			while(($file=readdir($dir))!==false)
			{
				if(!is_dir($basePath.DIRECTORY_SEPARATOR.$file) || $file==='.' || $file==='..' || $file==='source')
					continue;
				if(isset($this->languageNames[$file]))
					$this->_languages[$file]=$this->languageNames[$file];
			}
			ksort($this->_languages);
		}
		return $this->_languages;
	}
}
