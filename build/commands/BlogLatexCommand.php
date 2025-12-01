<?php

class BlogLatexCommand extends CConsoleCommand
{
	function getHelp()
	{
		return <<<EOD
USAGE
  yiic bloglatex

DESCRIPTION
  This command generates latex files for the definitive guide.
  The generated latex files are stored in the blog directory.

EOD;
	}

	function getSourceDir()
	{
		return dirname(__FILE__).'/../../docs/blog';
	}

	function getOutputDir()
	{
		return dirname(__FILE__).'/blog';
	}

	function run($args)
	{
		require_once(dirname(__FILE__).'/markdown/MarkdownHtml2Tex.php');
		$sourcePath=$this->getSourceDir();
		$chapters=$this->getTopics();
		$toc = '';
		foreach($chapters as $chapter=>$sections)
		{
			$toc .= sprintf("\chapter{%s}\n", $chapter);
			foreach($sections as $path=>$section)
			{
				echo "creating '$section'...";
				$content=file_get_contents($sourcePath."/{$path}.txt");
				$this->createLatexFile($chapter,$section,$content, $path);
				echo "done\n";
				$toc .= sprintf("\input{%s}\n", $path);
			}
		}
		$main_file = sprintf('%s/main.tex', $this->getOutputDir());
		file_put_contents($main_file, $toc);
	}

	function getTopics()
	{
		$file = $this->getSourceDir().'/toc.txt';
		$lines=file($file);
		$chapter='';
		$guideTopics=array();
		foreach($lines as $line)
		{
			if(($line=trim($line))==='')
				continue;
			if($line[0]==='*')
				$chapter=trim($line,'* ');
			else if($line[0]==='-' && preg_match('/\[(.*?)\]\((.*?)\)/',$line,$matches))
				$guideTopics[$chapter][$matches[2]]=$matches[1];
		}
		return $guideTopics;
	}

	function createLatexFile($chapter, $section, $content, $path)
	{
		$parser=new MarkdownParserLatex;
		$content=$parser->transform($content);
		$img_src = $this->getSourceDir().'/images';
		$img_dst = $this->getOutputDir();
		$html2tex = new MarkdownHtml2Tex($img_src, $img_dst);
		$tex = $html2tex->parse_html($content, $path);
		$filename = sprintf('%s/%s.tex', $this->getOutputDir(), $path);
		file_put_contents($filename, $tex);
	}
}
