<?php
/**
 * CHttpCookie class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * A CHttpCookie instance stores a single cookie, including the cookie name, value, domain, path, expire, and secure.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */
class CHttpCookie extends CComponent
{
	/**
	 * SameSite policy Lax will prevent the cookie from being sent by the browser in all cross-site browsing context
	 * during CSRF-prone request methods (e.g. POST, PUT, PATCH etc).
	 * E.g. a POST request from https://otherdomain.com to https://yourdomain.com will not include the cookie, however a GET request will.
	 * When a user follows a link from https://otherdomain.com to https://yourdomain.com it will include the cookie
	 * @see $sameSite
	 * @since 1.1.22
	 */
	const SAME_SITE_LAX='Lax';
	/**
	 * SameSite policy Strict will prevent the cookie from being sent by the browser in all cross-site browsing context
	 * regardless of the request method and even when following a regular link.
	 * E.g. a GET request from https://otherdomain.com to https://yourdomain.com or a user following a link from
	 * https://otherdomain.com to https://yourdomain.com will not include the cookie.
	 * @see $sameSite
	 * @since 1.1.22
	 */
	const SAME_SITE_STRICT='Strict';
	/**
	 * SameSite policy None will allow the cookie to be sent by the browser in all cross-site browsing context
	 * regardless of the request methods (e.g. POST, PUT, PATCH etc).
	 * E.g. a POST request from https://otherdomain.com to https://yourdomain.com or a user following a link from
	 * https://otherdomain.com to https://yourdomain.com it will include the cookie.
	 * @see $sameSite
	 * @since 1.1.23
	 */
	const SAME_SITE_NONE = 'None';

	/**
	 * @var string name of the cookie
	 */
	public $name;
	/**
	 * @var string value of the cookie
	 */
	public $value='';
	/**
	 * @var string domain of the cookie
	 */
	public $domain='';
	/**
	 * @var integer the timestamp at which the cookie expires. This is the server timestamp. Defaults to 0, meaning "until the browser is closed".
	 */
	public $expire=0;
	/**
	 * @var string the path on the server in which the cookie will be available on. The default is '/'.
	 */
	public $path='/';
	/**
	 * @var boolean whether cookie should be sent via secure connection
	 */
	public $secure=false;
	/**
	 * @var boolean whether the cookie should be accessible only through the HTTP protocol.
	 * By setting this property to true, the cookie will not be accessible by scripting languages,
	 * such as JavaScript, which can effectly help to reduce identity theft through XSS attacks.
	 * Note, this property is only effective for PHP 5.2.0 or above.
	 */
	public $httpOnly=false;
	/**
	 * @var array Cookie attribute "SameSite".
	 * @see https://www.owasp.org/index.php/SameSite
	 * This property only works for PHP 7.3.0 or above.
	 * @since 1.1.22
	 */
	public $sameSite=self::SAME_SITE_LAX;

	/**
	 * Constructor.
	 * @param string $name name of this cookie
	 * @param string $value value of this cookie
	 * @param array $options the configuration array consisting of name-value pairs
	 * that are used to configure this cookie
	 */
	public function __construct($name,$value,$options=array())
	{
		$this->name=$name;
		$this->value=$value;
		$this->configure($options);
	}
	/**
	 * This method can be used to configure the CookieObject with an array
	 * Note: you cannot use this method to set the name and/or the value of the cookie
	 * @param array $options the configuration array consisting of name-value pairs
	 * that are used to configure this cookie
	 * @since 1.1.11
	 */
	public function configure($options=array())
	{
		foreach($options as $name=>$value)
		{
			if($name==='name'||$name==='value')
				continue;
			$this->$name=$value;
		}
	}
	/**
	 * Magic method to use the cookie object as a string without having to call value property first.
	 * <code>
	 * $value = (string)$cookies['name'];
	 * </code>
	 * Note, that you still have to check if the cookie exists.
	 * @return string The value of the cookie. If the value property is null an empty string will be returned.
	 * @since 1.1.11
	 */
	public function __toString()
	{
		return (string)$this->value;
	}
}
