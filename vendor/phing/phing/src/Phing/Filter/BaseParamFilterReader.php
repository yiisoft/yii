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

namespace Phing\Filter;

use Exception;
use Phing\Io\FilterReader;
use Phing\Type\Parameter;
use Phing\Type\Parameterizable;

/**
 * Base class for core filter readers.
 *
 * @author    Yannick Lecaillez <yl@seasonfive.com>
 * @copyright 2003 seasonfive. All rights reserved
 *
 * @see     FilterReader
 */
class BaseParamFilterReader extends BaseFilterReader implements Parameterizable
{
    /**
     * The passed in parameter array.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Sets the parameters used by this filter, and sets
     * the filter to an uninitialized status.
     *
     * @param array $parameters Array of parameters to be used by this filter.
     *                          Should not be <code>null</code>.
     *
     * @throws Exception
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
        $this->setInitialized(false);
    }

    /**
     * Returns the parameters to be used by this filter.
     *
     * @return Parameter[] the parameters to be used by this filter
     */
    public function &getParameters()
    {
        return $this->parameters;
    }
}
