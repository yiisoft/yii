<?php
/**
 * CMarkdown class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require_once(Yii::getPathOfAlias('system.vendors.markdown.markdown').'.php');

/**
 * CMarkdown does syntax highlighting for its body content.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.widgets
 * @since 1.0
 */
class CMarkdown extends COutputProcessor
{
	/**
	 * @var string the CSS file used for the highlighter. If not set, a default CSS file will be used.
	 */
	public $cssFile;

	public $purifyOutput=false;

	/**
	 * Processes the captured output.
     * This method highlights the output according to the syntax of the specified {@link language}.
	 * @param string the captured output to be processed
	 */
	public function processOutput($output)
	{
		$output=$this->parse($output);
		parent::processOutput($output);
	}

	public function parse($output)
	{
		$cs=$this->getController()->getClientScript();
		if($this->cssFile===null)
		{
			$cssFile=Yii::getPathOfAlias('system.vendors.TextHighlighter.highlight').'.css';
			$cs->registerCssFile(CHtml::asset($cssFile));
		}
		else
			$cs->registerCssFile($this->cssFile);
		$parser=new CMarkdownParser;
		$output=$parser->transform($output);
		if($this->purifyOutput)
		{
			$purifier=new CHtmlPurifier;
			return $purifier->purify($output);
		}
		else
			return $output;
	}
}


class CMarkdownParser extends MarkdownExtra_Parser
{
	public function _doCodeBlocks_callback($matches)
	{
		$codeblock = $this->outdent($matches[1]);
		if(($codeblock = $this->getHighlightFencedCode($codeblock)) !== null)
			return "\n\n".$this->hashBlock($codeblock)."\n\n";
		else
			return parent::_doCodeBlocks_callback($matches);
	}

	public function getHighlightFencedCode($codeblock)
	{
		if(($highlight = $this->getHighlightTag($codeblock)) !== null)
		{
			$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);
			$codeblock = $this->highlightCodeBlock($highlight, $codeblock);
			if($codeblock)
				return "<div class=\"{$this->code_hl_class}\">$codeblock</div>";
		}
	}

	public function _doFencedCodeBlocks_callback($matches)
	{
		if(($codeblock = $this->getHighlightFencedCode($matches[2])) !== null)
			return "\n\n".$this->hashBlock($codeblock)."\n\n";
		else
			return parent::_doFencedCodeBlocks_callback($matches);
	}

	public function getHighlightTag($codeblock)
	{
		$str = trim(current(preg_split("/\r|\n/", $codeblock,2)));
		if(strlen($str) > 2 && $str[0] === '[' && $str[strlen($str)-1] === ']')
			return $str;
	}

	public function highlightCodeBlock($class, $code)
	{
		$hl = $this->getHighLighter($class);
		$class_len = strpos($code, $class)+strlen($class);
		$code = ltrim(substr($code, $class_len));
		if ($hl)
			return preg_replace('/<span\s+[^>]*>(\s*)<\/span>/', '\1', $hl->highlight($code));
		else
			return '<pre>'.htmlentities($code).'</pre>';
	}

	public function getHighLighter($options)
	{
		if(!class_exists('Text_Highlighter', false))
		{
			require_once(Yii::getPathOfAlias('system.vendors.TextHighlighter.Text.Highlighter').'.php');
			require_once(Yii::getPathOfAlias('system.vendors.TextHighlighter.Text.Highlighter.Renderer.Html').'.php');
		}
		$lang = current(preg_split('/\s+/', substr(substr($options,1), 0,-1),2));
		$highlighter = Text_Highlighter::factory($lang);
		if($highlighter)
			$highlighter->setRenderer(new Text_Highlighter_Renderer_Html($this->getHiglightConfig($options)));
		return $highlighter;
	}

	public function getHiglightConfig($options)
	{
		$config['use_language'] = true;
		if( $this->getInlineOptionValue('showLineNumbers', $options, false) )
			$config['numbers'] = HL_NUMBERS_LI;
		$config['tabsize'] = $this->getInlineOptionValue('tabSize', $options, 4);
		return $config;
	}

	public function getInlineOptionValue($name, $str, $defaultValue)
	{
		if(preg_match('/'.$name.'(\s*=\s*(\d+))?/i', $str, $v) && count($v) > 2)
			return $v[2];
		else
			return $defaultValue;
	}
}
