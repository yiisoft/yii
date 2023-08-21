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

namespace Phing\Dispatch;

use Phing\Task;

/**
 * Tasks extending this class may contain multiple actions.
 * The method that is invoked for execution depends upon the
 * value of the action attribute of the task.
 * <br>
 * Example:<br>
 * &lt;mytask action=&quot;list&quot;/&gt; will invoke the method
 * with the signature public function list() in mytask's class.
 * If the action attribute is not defined in the task or is empty,
 * the main() method will be called.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
abstract class DispatchTask extends Task implements Dispatchable
{
    private $action;

    /**
     * Get the action parameter name.
     *
     * @return string the <code>String</code> "action" by default (can be overridden)
     */
    public function getActionParameterName()
    {
        return 'action';
    }

    /**
     * Set the action.
     *
     * @param string $action the method name
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get the action.
     *
     * @return string the action
     */
    public function getAction()
    {
        return $this->action;
    }
}
