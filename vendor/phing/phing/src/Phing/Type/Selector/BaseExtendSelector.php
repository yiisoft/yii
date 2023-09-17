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

namespace Phing\Type\Selector;

/**
 * Convenience base class for all selectors accessed through ExtendSelector.
 * It provides support for gathering the parameters together as well as for
 * assigning an error message and throwing a build exception if an error is
 * detected.
 *
 * @author  Hans Lellelid, hans@xmpl.org (Phing)
 * @author  Bruce Atherton, bruce@callenish.com (Ant)
 */
abstract class BaseExtendSelector extends BaseSelector implements ExtendFileSelector
{
    /**
     * The passed in parameter array.
     */
    protected $parameters;

    /**
     * Set all the Parameters for this custom selector, collected by
     * the ExtendSelector class.
     *
     * @param array $parameters the complete set of parameters for this selector
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Allows access to the parameters gathered and set within the
     * &lt;custom&gt; tag.
     *
     * @return array the set of parameters defined for this selector
     */
    protected function getParameters()
    {
        return $this->parameters;
    }
}
