<?php
/**
* JSON (JavaScript Object Notation) is a lightweight data-interchange
* format. It is easy for humans to read and write. It is easy for machines
* to parse and generate. It is based on a subset of the JavaScript
* Programming Language, Standard ECMA-262 3rd Edition - December 1999.
* This feature can also be found in  Python. JSON is a text format that is
* completely language independent but uses conventions that are familiar
* to programmers of the C-family of languages, including C, C++, C#, Java,
* JavaScript, Perl, TCL, and many others. These properties make JSON an
* ideal data-interchange language.
*
* This package provides a simple encoder and decoder for JSON notation. It
* is intended for use with client-side Javascript applications that make
* use of HTTPRequest to perform server communication functions - data can
* be encoded into JSON notation for use in a client-side javascript, or
* decoded from incoming Javascript requests. JSON format is native to
* Javascript, and can be directly eval()'ed with no further parsing
* overhead
*
* All strings should be in ASCII or UTF-8 format!
*
* LICENSE: Redistribution and use in source and binary forms, with or
* without modification, are permitted provided that the following
* conditions are met: Redistributions of source code must retain the
* above copyright notice, this list of conditions and the following
* disclaimer. Redistributions in binary form must reproduce the above
* copyright notice, this list of conditions and the following disclaimer
* in the documentation and/or other materials provided with the
* distribution.
*
* THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED
* WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
* MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN
* NO EVENT SHALL CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
* INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
* BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
* OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
* ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
* TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
* USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
* DAMAGE.
*
* @author	  Michal Migurski <mike-json@teczno.com>
* @author	  Matt Knapp <mdknapp[at]gmail[dot]com>
* @author	  Brett Stimmerman <brettstimmerman[at]gmail[dot]com>
* @copyright   2005 Michal Migurski
* @license	 http://www.opensource.org/licenses/bsd-license.php
* @link		http://pear.php.net/pepr/pepr-proposal-show.php?id=198
*/

/**
 * CJSON converts PHP data to and from JSON format.
 *
 * @author	 Michal Migurski <mike-json@teczno.com>
 * @author	 Matt Knapp <mdknapp[at]gmail[dot]com>
 * @author	 Brett Stimmerman <brettstimmerman[at]gmail[dot]com>
 * @package	system.web.helpers
 * @since 1.0
 */
class CJSON
{
	/**
	 * Encodes an arbitrary variable into JSON format
     *
     * @deprecated use {@see \json_encode()}
     * @see \json_encode()
	 *
	 * @param mixed $var any number, boolean, string, array, or object to be encoded.
	 * If var is a string, it will be converted to UTF-8 format first before being encoded.
	 * @return string JSON string representation of input var
	 */
	public static function encode($var)
	{
	    return json_encode($var) ?: '';
	}

	/**
	 * decodes a JSON string into appropriate variable
	 *
     * @deprecated use {@see \json_decode()}
     * @see \json_decode()
     *
	 * @param string $str  JSON-formatted string
	 * @param boolean $useArray  whether to use associative array to represent object data
	 * @return mixed   number, boolean, string, array, or object corresponding to given JSON input string.
	 *    Note that decode() always returns strings in ASCII or UTF-8 format!
	 */
	public static function decode($str, $useArray=true)
	{
        return json_decode($str, $useArray);
	}
}
