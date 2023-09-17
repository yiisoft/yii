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

use Countable;
use Phing\Exception\BuildException;

/**
 * This is the base class for selectors that can contain other selectors.
 *
 * @author  <a href="mailto:bruce@callenish.com">Bruce Atherton</a> (Ant)
 */
abstract class BaseSelectorContainer extends BaseSelector implements SelectorContainer, Countable
{
    use SelectorAware;

    /**
     * Convert the Selectors within this container to a string. This will
     * just be a helper class for the subclasses that put their own name
     * around the contents listed here.
     *
     * @return string comma separated list of Selectors contained in this one
     */
    public function __toString()
    {
        $buf = '';
        $arr = $this->selectorElements();
        for ($i = 0, $size = count($arr); $i < $size; ++$i) {
            $buf .= (string) $arr[$i] . (isset($arr[$i + 1]) ? ', ' : '');
        }

        return $buf;
    }

    /**
     * <p>This implementation validates the container by calling
     * verifySettings() and then validates each contained selector
     * provided that the selector implements the validate interface.
     * </p>
     * <p>Ordinarily, this will validate all the elements of a selector
     * container even if the isSelected() method of some elements is
     * never called. This has two effects:</p>
     * <ul>
     * <li>Validation will often occur twice.
     * <li>Since it is not required that selectors derive from
     * BaseSelector, there could be selectors in the container whose
     * error conditions are not detected if their isSelected() call
     * is never made.
     * </ul>.
     */
    public function validate()
    {
        $this->verifySettings();
        $errmsg = $this->getError();
        if (null !== $errmsg) {
            throw new BuildException($errmsg);
        }
        foreach ($this->selectorsList as $o) {
            if ($o instanceof BaseSelector) {
                $o->validate();
            }
        }
    }
}
