<?php

class MarkdownParser extends CMarkdownParser
{
	private $_blockquoteType='';

	public function __construct()
	{
		$this->span_gamut += array(
			"doApiLinks" => 35,
		);

		parent::__construct();
	}

	public function safeTransform($content)
	{
		$content=$this->transform($content);
		$purifier=new CHtmlPurifier;
		return $purifier->purify($content);
	}

	public function _doHeaders_callback_setext($matches)
	{
		if ($matches[3] == '-' && preg_match('{^- }', $matches[1]))
			return $matches[0];
		$level = $matches[3]{0} == '=' ? 1 : 2;
		$text = $this->runSpanGamut($matches[1]);
		$attr = $this->doHeaderId($text);
		$block = "<h$level$attr>".$text."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	public function _doHeaders_callback_atx($matches)
	{
		$level = strlen($matches[1]);
		$text = $this->runSpanGamut($matches[2]);
		$attr = $this->doHeaderId($text);
		$block = "<h$level$attr>".$text."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	public function _doBlockQuotes_callback($matches)
	{
		$bq = $matches[1];
		# trim one level of quoting - trim whitespace-only lines
		$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
		$bq = $this->runBlockGamut($bq);		# recurse

		$bq = preg_replace('/^/m', "  ", $bq);
		# These leading spaces cause problem with <pre> content,
		# so we need to fix that:
		$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx',
			array(&$this, '_DoBlockQuotes_callback2'), $bq);

		# Do blockquote tips/notes
		$bq = preg_replace_callback('/^(\s*<p>\s*)([^:]+):\s*/sxi',
			array($this, 'doBlockquoteTypes'), $bq);
		$attr= $this->_blockquoteType ? " class=\"{$this->_blockquoteType}\"" : '';
		return "\n". $this->hashBlock("<blockquote{$attr}>\n$bq\n</blockquote>")."\n\n";
	}

	public function doHeaderId($text)
	{
		$id = preg_replace('/[^a-z0-9]/', '-', strtolower($text));
		$id = str_replace('/-{2,}/', '-', $id);
		return " id=\"$id\"";
	}

	public function doBlockquoteTypes($matches)
	{
		if(($pos=strpos($matches[2],'|'))!==false)
		{
			$type_str=substr($matches[2],$pos+1);
			$this->_blockquoteType=strtolower(substr($matches[2],0,$pos));
		}
		else
		{
			$this->_blockquoteType = strtolower($matches[2]);
			$type_str= ucwords($this->_blockquoteType);
		}
		return "<p><strong>$type_str:</strong> ";
	}

	public function doApiLinks($text)
	{
		return preg_replace_callback('/(?<!\])\[([^\]]+)\](?!\[)/', array($this, 'formatApiLinks'), $text);
	}

	public function formatApiLinks($match)
	{
		@list($text, $api) = explode('|', $match[1], 2);
		$api= $api===null ? $text: $api;
		$segs=explode('::',rtrim($api,'()'));
		$class=$segs[0];
		$anchor=isset($segs[1]) ? '#'.$segs[1] : '';
		$url = '/doc/api/'.$class.$anchor;
		$link = "<a href=\"$url\">$text</a>";
		return $this->hashPart($link);
	}
}