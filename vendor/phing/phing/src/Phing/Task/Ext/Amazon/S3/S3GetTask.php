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

namespace Phing\Task\Ext\Amazon\S3;

use Phing\Exception\BuildException;
use Phing\Task\Ext\Amazon\S3;

/**
 * Downloads an object off S3
 *
 * @package phing.tasks.ext
 * @author  Andrei Serdeliuc <andrei@serdeliuc.ro>
 * @extends Service_Amazon_S3
 */
class S3GetTask extends S3
{
    /**
     * This is where we'll store the object
     *
     * (default value: null)
     *
     * @var mixed
     */
    protected $objectTarget = null;

    /**
     * The S3 object we're working with
     *
     * (default value: null)
     *
     * @var mixed
     */
    protected $object = null;

    /**
     * @param $object
     * @throws BuildException
     */
    public function setObject($object)
    {
        if (empty($object) || !is_string($object)) {
            throw new BuildException('Object must be a non-empty string');
        }

        $this->object = $object;
    }

    /**
     * @return mixed
     * @throws BuildException
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new BuildException('Object is not set');
        }

        return $this->object;
    }

    /**
     * @param $target
     * @throws BuildException
     */
    public function setTarget($target)
    {
        if (!is_file($target) && !is_dir($target) && !is_link($target)) {
            if (!is_writable(dirname($target))) {
                throw new BuildException('Target is not writable: ' . $target);
            }
        } else {
            if (!is_writable($target)) {
                throw new BuildException('Target is not writable: ' . $target);
            }
        }

        $this->objectTarget = $target;
    }

    /**
     * @return mixed
     * @throws BuildException
     */
    public function getTarget()
    {
        if ($this->objectTarget === null) {
            throw new BuildException('Target is not set');
        }

        return $this->objectTarget;
    }

    public function execute()
    {
        $target = $this->getTarget();

        // Use the object name as the target if the current target is a directory
        if (is_dir($target)) {
            $target = rtrim($target, '/') . 'S3GetTask.php/' . $this->getObject();
        }

        $client = $this->getClientInstance();
        $client->getObject(
            [
                'Bucket' => $this->getBucket(),
                'Key' => $this->getObject(),
                'SaveAs' => $target
            ]
        );
    }
}
