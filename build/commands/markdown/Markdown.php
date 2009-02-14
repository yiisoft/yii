<?php

require_once(dirname(__FILE__).'/MarkdownParser.php');

class Markdown extends CMarkdown
{
	protected function createMarkdownParser()
	{
		return new MarkdownParser;
	}
}