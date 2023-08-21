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

namespace Phing\Util;

/**
 * Contains some shared attributes and methods -- and some abstract methods with
 * engine-specific implementations that sub-classes must override.
 *
 * @author  Hans Lellelid <hans@velum.net>
 */
interface RegexpEngine
{
    /**
     * Sets whether or not regex operation should ingore case.
     *
     * @param bool $bit
     */
    public function setIgnoreCase($bit);

    /**
     * Returns status of ignore case flag.
     *
     * @return bool
     */
    public function getIgnoreCase();

    /**
     * Sets whether regexp should be applied in multiline mode.
     *
     * @param bool $bit
     */
    public function setMultiline($bit);

    /**
     * Gets whether regexp is to be applied in multiline mode.
     *
     * @return bool
     */
    public function getMultiline();

    /**
     * Sets the maximum possible replacements for each pattern.
     *
     * @param int $limit
     */
    public function setLimit($limit);

    /**
     * Returns the maximum possible replacements for each pattern.
     *
     * @return int
     */
    public function getLimit();

    /**
     * Matches pattern against source string and sets the matches array.
     *
     * @param string $pattern the regex pattern to match
     * @param string $source  the source string
     * @param array  $matches the array in which to store matches
     *
     * @return bool success of matching operation
     */
    public function match($pattern, $source, &$matches);

    /**
     * Matches all patterns in source string and sets the matches array.
     *
     * @param string $pattern the regex pattern to match
     * @param string $source  the source string
     * @param array  $matches the array in which to store matches
     *
     * @return bool success of matching operation
     */
    public function matchAll($pattern, $source, &$matches);

    /**
     * Replaces $pattern with $replace in $source string.
     *
     * @param string $pattern the regex pattern to match
     * @param string $replace the string with which to replace matches
     * @param string $source  the source string
     *
     * @return string the replaced source string
     */
    public function replace($pattern, $replace, $source);
}
