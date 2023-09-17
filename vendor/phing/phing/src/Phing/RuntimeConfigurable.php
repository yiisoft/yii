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

namespace Phing;

use Exception;
use Phing\Exception\BuildException;
use Phing\Parser\ProjectConfigurator;

/**
 *  Wrapper class that holds the attributes of a Task (or elements
 *  nested below that level) and takes care of configuring that element
 *  at runtime.
 *
 *  <strong>SMART-UP INLINE DOCS</strong>
 *
 * @author  Andreas Aderhold <andi@binarycloud.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class RuntimeConfigurable
{
    private $elementTag;

    /**
     * @var array
     */
    private $children = [];

    /**
     * @var object|Task
     */
    private $wrappedObject;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var string
     */
    private $characters = '';

    /**
     * @var bool
     */
    private $proxyConfigured = false;

    /**
     * @param object|Task $proxy
     * @param mixed       $elementTag the element to wrap
     */
    public function __construct($proxy, $elementTag)
    {
        $this->wrappedObject = $proxy;
        $this->elementTag = $elementTag;

        if ($proxy instanceof Task) {
            $proxy->setRuntimeConfigurableWrapper($this);
        }
    }

    /**
     * @return object|Task
     */
    public function getProxy()
    {
        return $this->wrappedObject;
    }

    /**
     * @param object|Task $proxy
     */
    public function setProxy($proxy)
    {
        $this->wrappedObject = $proxy;
        $this->proxyConfigured = false;
    }

    /**
     * Set's the attributes for the wrapped element.
     *
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Returns the AttributeList of the wrapped element.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Adds child elements to the wrapped element.
     */
    public function addChild(RuntimeConfigurable $child)
    {
        $this->children[] = $child;
    }

    /**
     * Returns the child with index.
     *
     * @param int $index
     *
     * @return RuntimeConfigurable
     */
    public function getChild($index)
    {
        return $this->children[(int) $index];
    }

    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add characters from #PCDATA areas to the wrapped element.
     *
     * @param string $data
     */
    public function addText($data)
    {
        $this->characters .= (string) $data;
    }

    /**
     * Get the text content of this element. Various text chunks are
     * concatenated, there is no way (currently) of keeping track of
     * multiple fragments.
     *
     * @return string the text content of this element
     */
    public function getText()
    {
        return (string) $this->characters;
    }

    public function getElementTag()
    {
        return $this->elementTag;
    }

    /**
     * Reconfigure the element, even if it has already been configured.
     *
     * @param Project $p the project instance for this configuration
     */
    public function reconfigure(Project $p)
    {
        $this->proxyConfigured = false;
        $this->maybeConfigure($p);
    }

    /**
     * Configure the wrapped element and all children.
     *
     * @throws BuildException
     * @throws Exception
     */
    public function maybeConfigure(Project $project)
    {
        if ($this->proxyConfigured) {
            return;
        }

        $id = null;

        if ($this->attributes || (isset($this->characters) && '' !== $this->characters)) {
            ProjectConfigurator::configure($this->wrappedObject, $this->attributes, $project);

            if (isset($this->attributes['id'])) {
                $id = $this->attributes['id'];
            }

            if ('' !== $this->characters) {
                ProjectConfigurator::addText($project, $this->wrappedObject, $this->characters);
            }
            if (null !== $id) {
                $project->addReference($id, $this->wrappedObject);
            }
        }

        $this->proxyConfigured = true;
    }

    public function setAttribute(string $name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function removeAttribute(string $name)
    {
        unset($this->attributes[$name]);
    }

    public function setElementTag(string $name)
    {
        $this->elementTag = $name;
    }

    public function getId()
    {
        return $this->attributes['id'] ?? null;
    }
}
