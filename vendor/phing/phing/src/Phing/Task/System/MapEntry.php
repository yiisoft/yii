<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

namespace Phing\Task\System;

use Phing\Exception\BuildException;
use Phing\Util\StringHelper;

/**
 * Helper class, holds the nested &lt;map&gt; values. Elements will look like
 * this: &lt;map from=&quot;d:&quot; to=&quot;/foo&quot;/&gt;.
 *
 * When running on windows, the prefix comparison will be case
 * insensitive.
 */
class MapEntry
{
    /**
     * @var PathConvert
     */
    private $outer;

    public function __construct(PathConvert $outer)
    {
        $this->outer = $outer;
    }

    /**
     * the prefix string to search for; required.
     * Note that this value is case-insensitive when the build is
     * running on a Windows platform and case-sensitive when running on
     * a Unix platform.
     *
     * @param mixed $from
     */
    public function setFrom($from)
    {
        $this->outer->from = $from;
    }

    public function setTo($to)
    {
        $this->outer->to = $to;
    }

    /**
     * Apply this map entry to a given path element.
     *
     * @param string $elem Path element to process
     *
     * @throws BuildException
     *
     * @return string Updated path element after mapping
     */
    public function apply($elem)
    {
        if (null === $this->outer->from || null === $this->outer->to) {
            throw new BuildException(
                "Both 'from' and 'to' must be set "
                . 'in a map entry'
            );
        }

        // If we're on windows, then do the comparison ignoring case
        $cmpElem = $this->outer->onWindows ? strtolower($elem) : $elem;
        $cmpFrom = $this->outer->onWindows ? strtolower(
            str_replace('/', '\\', $this->outer->from)
        ) : $this->outer->from;

        // If the element starts with the configured prefix, then
        // convert the prefix to the configured 'to' value.

        if (StringHelper::startsWith($cmpFrom, $cmpElem)) {
            $len = strlen($this->outer->from);

            if ($len >= strlen($elem)) {
                $elem = $this->outer->to;
            } else {
                $elem = $this->outer->to . StringHelper::substring($elem, $len);
            }
        }

        return $elem;
    }
}
