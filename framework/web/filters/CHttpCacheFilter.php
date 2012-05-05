<?php
/**
 * CHttpCacheFilter class file.
 *
 * @author Da:Sourcerer <webmaster@dasourcerer.net>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CHttpCacheFilter implements http caching. It works a lot like {@link COutputCache}
 * as a filter, except that content caching is being done on the client side.
 *
 * @author Da:Sourcerer <webmaster@dasourcerer.net>
 * @version $Id$
 * @package system.web.filters
 * @since 1.1.11
 */
class CHttpCacheFilter extends CFilter
{
	/**
	 * Timestamp for the last modification date. Must be a string parsable by {@link http://php.net/strtotime strtotime()}.
	 * @var string
	 */
	public $lastModified;

	/**
	 * Expression for the last modification date. If set, this takes precedence over {@link lastModified}.
	 * @var string|callback
	 */
	public $lastModifiedExpression;
	/**
	 * Seed for the ETag. Can be anything that passes through {@link http://php.net/serialize serialize()}.
	 * @var mixed
	 */
	public $etagSeed;
	
	/**
	 * Expression for the ETag seed. If set, this takes precedence over {@link etag}. 
	 * @var string|callback
	 */
	public $etagSeedExpression;

	/**
	 * Http cache control headers
	 * @var string
	 */
	public $cacheControl = 'max-age=3600, public';
	
	public function preFilter($filterChain)
	{
		// Only cache GET and HEAD requests
		if(!in_array(Yii::app()->getRequest()->getRequestType(), array('GET', 'HEAD')))
			return true;
		
		if($this->lastModifiedExpression)
		{
			$value=$this->evaluateExpression($this->lastModifiedExpression);
			if(($lastModified=strtotime($value))===false)
				throw new CException(Yii::t('yii','Invalid expression for CHttpCacheFilter.lastModifiedExpression: The evaluation result could not be understood by strtotime()'));
		}
		else
		{
			if(($lastModified=strtotime($this->lastModified))===false)
				throw new CException(Yii::t('yii','CHttpCacheFilter.lastModified contained a value that could not be understood by strtotime()'));
		}
		
		if($this->etagSeedExpression)
			$etag=$this->generateEtag($this->evaluateExpression($this->etagSeedExpression));
		else
			$etag=$this->generateEtag($this->etagSeed);

		if($etag===null&&$lastModified===null)
			return true;
		
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])&&isset($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			if($this->checkLastModified($lastModified)&&$this->checkEtag($etag))
			{
				$this->send304();
				return false;
			}
		}
		else if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			if($this->checkLastModified($lastModified))
			{
				$this->send304();
				return false;
			}
		}
		else if(isset($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			if($this->checkEtag($etag))
			{
				$this->send304();
				return false;
			}
			
		}
				
		if($lastModified)
			header('Last-Modified: '.date('r', $lastModified));
		
		if($etag)
			header('ETag: '.$etag);
		
		header('Cache-Control: ' . $this->cacheControl);
		return true;
	}
	
	private function checkEtag($etag)
	{
		return isset($_SERVER['HTTP_IF_NONE_MATCH'])&&$_SERVER['HTTP_IF_NONE_MATCH']==$etag;
	}
	
	private function checkLastModified($lastModified)
	{
		return isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])&&strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])>=$lastModified;
	}

	/**
	 * Send the 304 HTTP status code to the client
	 */
	private function send304()
	{
		header($_SERVER['SERVER_PROTOCOL'].' 304 Not modified');
	}
	
	/**
	 * Generates a quoted string out of the seed
	 * @param mixed $seed Seed for the ETag
	 */
	private function generateEtag($seed)
	{
		return '"'.base64_encode(sha1(serialize($seed), true)).'"';
	}
}
