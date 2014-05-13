<?php

require_once(Yii::getPathOfAlias('system.vendors.TextHighlighter.Text.Highlighter.Renderer.Array').'.php');
require_once(dirname(__FILE__).'/Markdown.php');

class Text_Highlighter_Renderer_Latex extends Text_Highlighter_Renderer_Array
{
	var $tex_chars = array
	(
		'\\' => '\(\backslash\)',
		'#'	 => '\#',
		'$'  => '\$',
		'%'  => '\%',
		'&'  => '\&',
		'_'  => '\_',
		'{'  => '\{',
		'}'  => '\}',
		'^'  => '\^{}',
		'~'  => '\~{}',
	);

    function finalize()
    {
        // get parent's output
        parent::finalize();
        $output = parent::getOutput();
		$tex_output = '';
        foreach ($output AS $token)
		{
            if ($this->_enumerated)
			{
                $class = $token[0];
                $content = $token[1];
            }
			else
			{
                $key = key($token);
                $class = $key;
                $content = $token[$key];
            }
            $iswhitespace = ctype_space($content);
            if (!$iswhitespace)
			{
				if ($class === 'special')
					$class = 'code';
                $tex_output .= sprintf('\textcolor{%s}{%s}', $class, $this->escape($content));
			}
			else
                $tex_output .= $content;
        }
		$this->_output = "\\begin{alltt}\n" . $tex_output . "\\end{alltt}";
	}

	function escape($str)
	{
		return str_replace(array_keys($this->tex_chars), array_values($this->tex_chars), html_entity_decode($str));
	}
}

class MarkdownParserLatex extends MarkdownParser
{
	protected function createHighLighter($options)
	{
		$hi = parent::createHighLighter($options);
		$renderer = new Text_Highlighter_Renderer_Latex($this->getHiglightConfig($options));
		$hi->setRenderer($renderer);
		return $hi;
	}
}

class MarkdownHtml2Tex
{
	var $path;
	var $has_path_label=false;

	var $img_src_dir;
	var $img_dst_dir;

	function __construct($img_src_dir, $img_dst_dir)
	{
		$this->img_src_dir = $img_src_dir;
		$this->img_dst_dir = $img_dst_dir;
	}

	function escape($str)
	{
		return str_replace(array_keys($this->tex_chars), array_values($this->tex_chars), html_entity_decode($str));
	}

	var $tex_chars = array
	(
		'\\' => '\(\backslash\)',
		'#'	 => '\#',
		'$'  => '\$',
		'%'  => '\%',
		'&'  => '\&',
		'_'  => '\_',
		'{'  => '\{',
		'}'  => '\}',
		'^'  => '\^{}',
		'~'  => '\~{}',
	);

	var $texttt_hyphen = array
	(
		//'\\' => '\bshyp{}',
		'/'  => '\fshyp{}',
		'.'  => '\dothyp{}',
		':'  => '\colonhyp{}',
	);

	function hypenat($text)
	{
		return str_replace(array_keys($this->texttt_hyphen), array_values($this->texttt_hyphen), $text);
	}

	function escape_verbatim($matches)
	{
		$text = html_entity_decode($matches[1]);
		return "\n\\begin{footnotesize}\\begin{verbatim}".$text."\\end{verbatim}\\end{footnotesize}\n";
	}

	function escape_syntax($matches)
	{
		$text = html_entity_decode($matches[1]);
		return "\n\\begin{footnotesize}".$text."\\end{footnotesize}\n";
	}

	function escape_verb($matches)
	{

		$text = $this->hypenat($this->escape($matches[1]));
		return sprintf('\texttt{\small %s}',$text);
	}

	function include_image($matches)
	{
		$image = $this->img_src_dir.'/'.trim($matches[1]);
		$file = realpath($image);
		$info = getimagesize($file);
		switch($info[2])
		{
			case 1:
				$im = imagecreatefromgif($file);
				break;
			case 2: $im = imagecreatefromjpeg($file); break;
			case 3: $im = imagecreatefrompng($file); break;
		}
		if(isset($im))
		{
			$fileinfo = pathinfo($file);
			$filename = str_replace('.'.$fileinfo['extension'], '.png', $fileinfo['basename']);
			$newfile = $this->img_dst_dir.'/'.$filename;
			imagepng($im,$newfile);
			imagedestroy($im);
			return $this->include_figure($info, $filename, $matches);
		}
	}

	function include_figure($info, $filename, $matches)
	{
		$width = sprintf('%0.2f', $info[0]/(135/2.54));
		$caption = $this->escape($matches[2]);
		return <<<TEX
\\begin{figure}[htbp]
  \\centering
  \\includegraphics[width={$width}cm]{{$filename}}
  \\label{fig:{$filename}}
  \\caption{{$caption}}
\\end{figure}
TEX;
	}

	function make_link($matches)
	{
		if(strpos($matches[1], '/doc/api/')===0)
		{
			$url = 'http://www.yiiframework.com' . $matches[1];
			return sprintf('\href{%s}{%s}', $this->escape($url), $this->escape($matches[2]));
		}
		else if (strpos($matches[1], 'http://')===0)
		{
			return sprintf('\href{%s}{%s}', $this->escape($matches[1]), $this->escape($matches[2]));
		}
		else if (strpos($matches[1], '/doc/guide/')===0)
		{
			$href = str_replace('/doc/guide/','',$matches[1]);
			$href = str_replace('#', '-', $href);
			return sprintf('\hyperref[%s]{%s}', $this->escape($href), $this->escape($matches[2]));
		}
		else if (strpos($matches[1], '/doc/blog/')===0)
		{
			$href = str_replace('/doc/blog/','',$matches[1]);
			$href = str_replace('#', '-', $href);
			return sprintf('\hyperref[%s]{%s}', $this->escape($href), $this->escape($matches[2]));
		}
	}

	function make_sections($matches)
	{
		$label = sprintf('\label{%s}', $this->path.'-'.$matches[2]);
		if(!$this->has_path_label)
		{
			$label .= sprintf("\n\\label{%s}", $this->path);
			$this->has_path_label = true;
		}
		$section = sprintf('\%ssection{%s}', str_repeat('sub',intval($matches[1])-1), $matches[3]);
		return $section.$label;
	}

	function parse_html($html, $path)
	{
		$this->path = $path;

		$html = preg_replace('/<\/?p [^>]*>/', '', $html);
		$html = preg_replace('/<\/?p>/', '', $html);

		$html = preg_replace('/(\d+)%/', '\1\%', $html);

		$html = preg_replace_callback('/<img\s+src="([^"]+)"\s+alt="([^"]+)"[^>]*\/>/', array($this, 'include_image'), $html);

		$html = preg_replace_callback('#<div class="hl-code">((.|\n)*?)</div>#', array($this, 'escape_syntax'), $html);
		$html = preg_replace_callback('/<code>([^<]*)<\/code>/', array($this,'escape_verb'), $html);
		$html = preg_replace_callback('/<pre>([^<]*)<\/pre>/', array($this,'escape_verbatim'), $html);

		//text modifiers
		$html = preg_replace('/<(b|strong)[^>]*>([^<]*)<\/(b|strong)>/', '\textbf{$2}', $html);
		$html = preg_replace('/<(i|em)>([^<]*)+?<\/(i|em)>/', '\emph{$2}', $html);
		$html = preg_replace_callback('/<tt>([^<]*)<\/tt>/', array($this,'escape_verb'), $html);

		//links
		$html = preg_replace_callback('/<a[^>]+href="([^"]*)"[^>]*>([^<]*)<\/a>/', array($this,'make_link'), $html);

		//description <dl>
		$html = preg_replace('/<dt>([^<]*)<\/dt>/', '\item[$1]', $html);
		$html = preg_replace('/<\/?dd>/', '', $html);
		$html = preg_replace('/<dl>/', '\begin{description}', $html);
		$html = preg_replace('/<\/dl>/', '\end{description}', $html);

		//item lists
		$html = preg_replace('/<ul[^>]*>/', '\begin{itemize}', $html);
		$html = preg_replace('/<\/ul>/', '\end{itemize}', $html);
		$html = preg_replace('/<ol[^>]*>/', '\begin{enumerate}', $html);
		$html = preg_replace('/<\/ol>/', '\end{enumerate}', $html);
		$html = preg_replace('/<li[^>]*>/', '\item ', $html);
		$html = preg_replace('/<\/li>/', '', $html);

		//headings
		$html = preg_replace_callback('/<h(1|2|3)\s+id="([^"]+)"[^>]*>([^<]+)<\/h(1|2|3)>/', array($this, 'make_sections'), $html);

		//tip box
		$html = preg_replace_callback('/<blockquote class="([^"]+)">((.|\n)*?)<\/blockquote>/', array($this, 'mbox'), $html);

		$html = preg_replace('/<div class="revision">((.|\n)*?)<\/div>/', '', $html);

		return $html;
	}

	function mbox($matches)
	{
		return "\n\\begin{tipbox}\n".$matches[2]."\n\\end{tipbox}\n";
	}
}