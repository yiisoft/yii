<?php
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
		
		if($this->lastModified || $this->lastModifiedExpression)
		{
			if($this->lastModifiedExpression)
			{
				$value=$this->evaluateExpression($this->lastModifiedExpression);
				if(($lastModified=strtotime($value))===false)
					throw new CException("HttpCacheFilter.lastModifiedExpression evaluated to '{$value}' which could not be understood by strtotime()");
			}
			else
			{
				if(($lastModified=strtotime($this->lastModified))===false)
					throw new CException("HttpCacheFilter.lastModified contained '{$this->lastModified}' which could not be understood by strottime()");
			}
			
			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])>=$lastModified)
			{
				$this->send304();
				return false;
			}
			
			header('Last-Modified: '.date('r', $lastModified));
		}
		elseif($this->etagSeed || $this->etagSeedExpression)
		{
			if($this->etagSeedExpression)
				$etag=$this->generateEtag($this->evaluateExpression($this->etagSeedExpression));
			else
				$etag=$this->generateEtag($this->etagSeed);
			
			if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH']==$etag)
			{
				$this->send304();
				return false;
			}
			
			header('ETag: '.$etag);
		}
		
		header('Cache-Control: ' . $this->cacheControl);
		return true;
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
